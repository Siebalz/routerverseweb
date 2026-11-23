<?php

namespace App\Http\Controllers;

use App\Services\LoginOtpService;
use Illuminate\Http\Request;

/** Halaman "Keamanan Akun": status email dan verifikasi dua langkah (kode OTP lewat email). */
class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('settings.security', [
            'user' => $user,
            'otpAvailable' => (bool) config('security.otp.enabled'),
            'otpForced' => $user->otpRequiredByRole(),
            'otpOn' => $user->requiresOtp(),
        ]);
    }

    public function enable(Request $request)
    {
        if (! config('security.otp.enabled')) {
            return back()->with('error', 'Verifikasi dua langkah sedang dinonaktifkan oleh admin.');
        }

        $request->validate(['password' => ['required', 'current_password']], [
            'password.required' => 'Masukkan password Anda untuk konfirmasi.',
            'password.current_password' => 'Password salah.',
        ]);

        $request->user()->forceFill(['otp_enabled' => true])->save();

        return back()->with('success', 'Verifikasi dua langkah aktif. Mulai login berikutnya Anda akan diminta kode dari email.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        if ($user->otpRequiredByRole()) {
            return back()->with('error', 'Akun admin wajib memakai verifikasi dua langkah.');
        }

        $request->validate(['password' => ['required', 'current_password']], [
            'password.required' => 'Masukkan password Anda untuk konfirmasi.',
            'password.current_password' => 'Password salah.',
        ]);

        $user->forceFill(['otp_enabled' => false])->save();
        app(LoginOtpService::class)->forget($user);

        return back()->with('success', 'Verifikasi dua langkah dimatikan.');
    }
}
