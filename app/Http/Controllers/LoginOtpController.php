<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Halaman kode OTP saat login. Hanya bisa dibuka setelah email dan password benar. */
class LoginOtpController extends Controller
{
    /** Batas waktu menyelesaikan alur login: setelah ini harus mulai dari awal. */
    protected const PENDING_MINUTES = 30;

    public function __construct(protected LoginOtpService $otp)
    {
    }

    protected function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('otp_pending');

        if (! is_array($pending) || (now()->timestamp - (int) ($pending['at'] ?? 0)) > self::PENDING_MINUTES * 60) {
            $request->session()->forget('otp_pending');

            return null;
        }

        return User::find($pending['user_id'] ?? 0);
    }

    protected function expired()
    {
        return redirect()->route('login')->withErrors(['email' => 'Sesi verifikasi berakhir. Silakan masuk lagi.']);
    }

    public function show(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $this->expired();
        }

        [$local, $domain] = array_pad(explode('@', $user->email, 2), 2, '');

        return view('auth.otp', [
            'maskedEmail' => mb_substr($local, 0, 2).str_repeat('*', max(1, mb_strlen($local) - 2)).'@'.$domain,
            'ttl' => $this->otp->ttlMinutes(),
            'resendSeconds' => $this->otp->resendSeconds(),
            'wait' => $this->otp->secondsUntilResend($user),
        ]);
    }

    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $this->expired();
        }

        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Masukkan kode 6 digit dari email.',
            'code.digits' => 'Kode terdiri dari 6 angka.',
        ]);

        switch ($this->otp->verify($user, (string) $request->input('code'))) {
            case LoginOtpService::OK:
                $request->session()->forget('otp_pending');
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'));

            case LoginOtpService::INVALID:
                $left = $this->otp->attemptsLeft($user);

                return back()->withErrors(['code' => "Kode salah. Sisa percobaan: {$left}."]);

            case LoginOtpService::EXPIRED:
                return back()->withErrors(['code' => 'Kode sudah kedaluwarsa. Minta kode baru di bawah.']);

            case LoginOtpService::LOCKED:
                $request->session()->forget('otp_pending');

                return redirect()->route('login')->withErrors(['email' => 'Terlalu banyak kode yang salah. Silakan masuk lagi untuk mendapat kode baru.']);

            default:
                return back()->withErrors(['code' => 'Belum ada kode aktif. Minta kode baru di bawah.']);
        }
    }

    public function resend(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return $this->expired();
        }

        $wait = $this->otp->secondsUntilResend($user);
        if ($wait > 0) {
            return back()->withErrors(['code' => "Tunggu {$wait} detik sebelum meminta kode baru."]);
        }

        try {
            $this->otp->issue($user);
        } catch (\Throwable $e) {
            return back()->withErrors(['code' => 'Kode gagal dikirim. Coba lagi sebentar atau hubungi admin.']);
        }

        return back()->with('success', 'Kode baru sudah dikirim ke email Anda.');
    }

    public function cancel(Request $request)
    {
        $request->session()->forget('otp_pending');

        return redirect()->route('login');
    }
}
