<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LoginOtpService;
use Illuminate\Console\Command;

class UserVerify extends Command
{
    protected $signature = 'user:verify {email : Email akun} {--otp-off : Matikan juga verifikasi dua langkah milik akun ini}';

    protected $description = 'Tandai email akun sebagai terverifikasi (untuk keadaan darurat, misalnya email belum bisa terkirim)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        $this->info("Email {$user->email} ditandai terverifikasi.");

        if ($this->option('otp-off')) {
            $user->forceFill(['otp_enabled' => false])->save();
            app(LoginOtpService::class)->forget($user);
            $this->info('Verifikasi dua langkah pilihan akun ini dimatikan.');
            if ($user->otpRequiredByRole()) {
                $this->warn('Akun admin tetap diminta kode OTP. Untuk melewatinya sementara, isi AUTH_OTP_ENABLED=false di .env lalu jalankan php artisan config:cache.');
            }
        }

        return self::SUCCESS;
    }
}
