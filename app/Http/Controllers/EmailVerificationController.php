<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Verifikasi email setelah daftar. Tautan di email bertanda tangan (signed) dan berlaku 60 menit. */
class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', ['email' => $request->user()->email]);
    }

    /** Tidak butuh login: tautan dibuka di perangkat mana pun, keamanannya ada di tanda tangan dan hash email. */
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (Auth::check()) {
            return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi.');
        }

        return redirect()->route('login')->with('success', 'Email berhasil diverifikasi. Silakan masuk.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Email gagal dikirim. Coba lagi sebentar atau hubungi admin.');
        }

        return back()->with('success', 'Email verifikasi dikirim ulang. Periksa kotak masuk dan folder spam.');
    }
}
