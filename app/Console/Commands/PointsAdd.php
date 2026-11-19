<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PointService;
use Illuminate\Console\Command;

class PointsAdd extends Command
{
    protected $signature = 'points:add {email} {amount : Rupiah, boleh negatif untuk mengurangi} {--note=Penyesuaian admin}';

    protected $description = 'Tambah atau kurangi poin pelanggan (sementara, sebelum top up Duitku aktif)';

    public function handle(PointService $points): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Pengguna tidak ditemukan.');

            return self::FAILURE;
        }
        $amount = (int) $this->argument('amount');
        if ($amount === 0) {
            $this->error('Jumlah tidak boleh 0.');

            return self::FAILURE;
        }
        try {
            $amount > 0
                ? $points->credit($user, $amount, 'adjust', $this->option('note'))
                : $points->debit($user, -$amount, 'adjust', $this->option('note'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->info("Saldo {$user->email}: ".number_format($user->points, 0, ',', '.'));

        return self::SUCCESS;
    }
}
