<?php

/*
 * Keamanan masuk akun: verifikasi email saat daftar dan kode OTP lewat email
 * sebagai verifikasi kedua saat login.
 *
 * Semua bisa dimatikan lewat .env bila email belum siap (lihat docs/AUTENTIKASI.md).
 */
return [
    // Akun baru wajib mengklik tautan di email sebelum memakai dashboard.
    'email_verification' => (bool) env('AUTH_EMAIL_VERIFICATION', true),

    // Turnstile aktif bila kunci di config/services.php terisi dan saklar ini true.
    'turnstile' => [
        'enabled' => (bool) env('TURNSTILE_ENABLED', true),
    ],

    'otp' => [
        // Saklar utama kode OTP. false = tidak ada yang diminta kode (untuk keadaan darurat).
        'enabled' => (bool) env('AUTH_OTP_ENABLED', true),

        // Admin selalu diminta kode OTP. Pelanggan boleh mengaktifkannya sendiri di "Keamanan Akun".
        'admin_required' => (bool) env('AUTH_OTP_ADMIN_REQUIRED', true),

        'ttl_minutes' => (int) env('AUTH_OTP_TTL', 10),
        'max_attempts' => (int) env('AUTH_OTP_MAX_ATTEMPTS', 5),
        'resend_seconds' => (int) env('AUTH_OTP_RESEND_SECONDS', 60),
    ],
];
