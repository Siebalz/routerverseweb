<?php

namespace App\Console\Commands;

use App\Models\Topup;
use App\Services\DuitkuPop;
use App\Services\TopupSettler;
use Illuminate\Console\Command;

class DuitkuTest extends Command
{
    protected $signature = 'duitku:test {--settle= : merchant_order_id top up yang sudah Anda pastikan LUNAS di dashboard Duitku, untuk memasukkan poinnya secara manual}';

    protected $description = 'Cek konfigurasi Duitku, alamat callback, dan top up terakhir; bisa menyelesaikan top up manual';

    public function handle(TopupSettler $settler): int
    {
        if ($id = $this->option('settle')) {
            $t = Topup::where('merchant_order_id', $id)->first();
            if (! $t) {
                $this->error('Top up tidak ditemukan.');

                return self::FAILURE;
            }
            $r = $settler->settle($t, '00', null, 'manual admin');
            $this->info($r === 'credited' ? "Poin Rp {$t->amount} dimasukkan untuk user #{$t->user_id}." : "Tidak ada perubahan ({$r}).");

            return self::SUCCESS;
        }

        $ok = true;
        $d = DuitkuPop::make();
        $this->line('DUITKU_ENV            : '.config('duitku.env'));
        $this->line('DUITKU_MERCHANT_CODE  : '.(config('duitku.merchant_code') ? config('duitku.merchant_code') : '(KOSONG)'));
        $this->line('DUITKU_API_KEY        : '.(config('duitku.api_key') ? 'terisi' : '(KOSONG)'));
        if (! $d->configured()) {
            $this->error('Merchant code atau API key kosong. Isi di .env lalu jalankan php artisan config:clear.');
            $ok = false;
        }

        $cb = DuitkuPop::callbackUrl();
        $this->line("Alamat callback       : {$cb}");
        if (preg_match('#^https?://(localhost|127\.|0\.0\.0\.0|.*\.test|.*\.local)#i', $cb)) {
            $this->error('Callback mengarah ke komputer lokal. Duitku tidak bisa menjangkaunya, jadi poin tidak akan bertambah. Isi APP_URL dengan alamat publik (tunnel atau domain HTTPS) lalu php artisan config:clear. Lihat docs/DUITKU.md bagian uji lokal.');
            $ok = false;
        } elseif (str_starts_with($cb, 'http://')) {
            $this->warn('Callback memakai http. Sebaiknya HTTPS, dan APP_URL harus sama dengan domain publik.');
        }
        $this->line('Pastikan juga alamat ini dibuka dari internet (tanpa login) dan sama dengan Callback URL di dashboard Duitku.');

        $this->line('');
        $this->line('Top up terakhir:');
        foreach (Topup::latest('id')->take(5)->get() as $t) {
            $this->line("  {$t->merchant_order_id}  Rp {$t->amount} (bayar Rp {$t->total})  {$t->status}  ref=".($t->reference ?: '-'));
        }
        $this->line('Log callback: storage/logs/laravel.log (cari "Duitku callback"). Kalau tidak ada baris itu, callback tidak sampai ke server.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
