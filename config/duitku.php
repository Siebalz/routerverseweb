<?php

/*
 * Duitku POP (https://docs.duitku.com/pop/id). Kunci HANYA di .env, jangan di repo.
 * DUITKU_ENV=sandbox untuk uji coba, production untuk sungguhan.
 */
return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key' => env('DUITKU_API_KEY', ''),
    'env' => env('DUITKU_ENV', 'sandbox'), // sandbox | production

    // Top up poin (1 poin = Rp 1). Biaya ditanggung pembeli: total bayar = nominal + biaya.
    // Biaya = flat + persen dari nominal (dibulatkan ke atas). Isi sesuai biaya sebenarnya dari Duitku.
    'min_topup' => (int) env('DUITKU_MIN_TOPUP', 10000),
    'max_topup' => (int) env('DUITKU_MAX_TOPUP', 5000000),
    'fee_flat' => (int) env('DUITKU_FEE_FLAT', 0),
    'fee_percent' => (float) env('DUITKU_FEE_PERCENT', 0),

    // Masa berlaku tagihan dalam menit.
    'expiry_minutes' => (int) env('DUITKU_EXPIRY_MINUTES', 60),
];
