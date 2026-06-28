<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan setiap hari jam 00:05 untuk menandai layanan yang sudah
// lewat masa aktif (1 bulan) sebagai "expired" secara otomatis.
Schedule::command('orders:expire')->dailyAt('00:05');
