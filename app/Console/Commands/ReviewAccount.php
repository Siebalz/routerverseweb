<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PointService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReviewAccount extends Command
{
    protected $signature = 'user:review-account
        {email=uji.duitku@routerverse.web.id : Email akun uji}
        {--password= : Sandi (kosong = dibuat acak)}
        {--points=100000 : Saldo poin awal, Rupiah}';

    protected $description = 'Buat atau perbarui akun pelanggan uji untuk reviewer Duitku (email terverifikasi, tanpa OTP, ada saldo)';

    public function handle(PointService $points): int
    {
        $password = $this->option('password') ?: Str::password(12, symbols: false);

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->name = $user->name ?: 'Akun Uji Duitku';
        $user->password = $password;
        $user->role = 'user';
        $user->otp_enabled = false;
        $user->email_verified_at = $user->email_verified_at ?: now();
        $user->save();

        $target = (int) $this->option('points');
        $topUp = $target - (int) $user->points;
        if ($topUp > 0) {
            $points->credit($user, $topUp, 'adjust', 'Saldo akun uji reviewer');
        }

        $this->info('Akun uji siap (tidak ada masa kedaluwarsa akun).');
        $this->line('Login : https://routerverse.web.id/login');
        $this->line('Email : '.$user->email);
        $this->line('Sandi : '.$password);
        $this->line('Saldo : Rp '.number_format((int) $user->fresh()->points, 0, ',', '.'));

        return self::SUCCESS;
    }
}
