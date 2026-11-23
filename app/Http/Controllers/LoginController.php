<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginOtpService;
use App\Services\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Menampilkan halaman login
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cloudflare Turnstile: robot ditolak sebelum password diperiksa (menghemat CPU dan tidak menghitung sebagai percobaan akun).
        if (! app(Turnstile::class)->verify($request->input('cf-turnstile-response'), $request->ip())) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['captcha' => 'Verifikasi keamanan gagal. Tunggu kotak verifikasi selesai, lalu coba lagi.']);
        }

        // Kunci pembatas berdasarkan kombinasi email + IP, biar 1 orang nyoba
        // banyak akun dari 1 IP atau nyoba 1 akun dari banyak tempat tetap kena limit.
        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        // Maksimal 5x salah dalam 1 menit, kalau lebih -> diblok sementara
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        // Cek email dan password saja dulu. Sesi baru dibuka setelah semua verifikasi lolos.
        if (Auth::validate($credentials) && ($user = User::where('email', $credentials['email'])->first())) {
            // Password benar -> reset penghitung supaya tidak ikut menghukum user yang sah
            RateLimiter::clear($throttleKey);

            // Verifikasi kedua: kode OTP dari email (wajib untuk admin, opsional untuk pelanggan).
            if ($user->requiresOtp()) {
                return $this->startOtp($request, $user);
            }

            Auth::login($user);
            $request->session()->regenerate();

            // ✅ Setelah login, langsung arahkan ke dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Login gagal -> tambah hitungan, kunci 60 detik setelah mencapai batas
        RateLimiter::hit($throttleKey, 60);

        // Jika gagal login
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /** Simpan status "menunggu kode OTP" di sesi, kirim kodenya, lalu arahkan ke halaman kode. */
    protected function startOtp(Request $request, User $user)
    {
        $otp = app(LoginOtpService::class);

        $request->session()->regenerate();
        $request->session()->put('otp_pending', ['user_id' => $user->id, 'at' => now()->timestamp]);

        // Kode yang baru dikirim masih berlaku: jangan kirim lagi, supaya halaman login tidak bisa dipakai membanjiri email pemilik akun.
        if ($otp->secondsUntilResend($user) > 0) {
            return redirect()->route('login.otp');
        }

        try {
            $otp->issue($user);
        } catch (\Throwable $e) {
            $request->session()->forget('otp_pending');

            return back()->withErrors([
                'email' => 'Kode verifikasi gagal dikirim ke email Anda. Coba lagi sebentar atau hubungi admin.',
            ])->onlyInput('email');
        }

        return redirect()->route('login.otp');
    }

    public function logout(Request $request)
    {
        // Logout user
        Auth::logout();

        // Hapus sesi
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login
        return redirect('/');
    }
}
