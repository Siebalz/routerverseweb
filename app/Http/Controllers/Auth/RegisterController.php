<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // Tampilkan form register
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Proses register
    public function register(Request $request)
    {
        // Cloudflare Turnstile: tolak robot sebelum menyentuh database.
        if (! app(Turnstile::class)->verify($request->input('cf-turnstile-response'), $request->ip())) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['captcha' => 'Verifikasi keamanan gagal. Tunggu kotak verifikasi selesai, lalu coba lagi.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // User pertama yang daftar otomatis jadi admin (bisa kelola produk)
            'role' => User::count() === 0 ? 'admin' : 'user',
        ]);

        // Tanpa verifikasi email: alur lama, langsung ke halaman login.
        if (! config('security.email_verification')) {
            $user->forceFill(['email_verified_at' => now()])->save();

            return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
        }

        // Kirim tautan verifikasi, lalu masuk ke halaman "cek email".
        $mailFailed = false;
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            $mailFailed = true;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')->with(
            $mailFailed ? 'error' : 'success',
            $mailFailed
                ? 'Akun sudah dibuat, tetapi email verifikasi gagal dikirim. Tekan "Kirim ulang email" di bawah.'
                : 'Akun dibuat. Kami sudah mengirim tautan verifikasi ke email Anda.'
        );
    }
}
