<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;

/**
 * Seperti middleware "verified" bawaan Laravel, tetapi bisa dimatikan lewat
 * AUTH_EMAIL_VERIFICATION=false (config/security.php) bila email belum siap.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (config('security.email_verification')
            && $user instanceof MustVerifyEmail
            && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Email Anda belum diverifikasi.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
