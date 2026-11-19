<?php

/*
 * VPN Remote: daftar server, harga, dan akses RouterOS API tiap server.
 * GANTI nilai contoh di bawah (atau isi lewat .env) sebelum dipakai.
 *
 * 'host'      nama domain yang ditampilkan ke pelanggan, dipakai di alamat remote dan sebagai
 *             tujuan L2TP di script client. Record DNS-nya harus mengarah ke IP publik server VPN.
 * 'public_ip' IP publik server untuk aturan NAT (kosongkan bila sama dengan host)
 * 'api'       akun MikroTik server VPN. Butuh policy: api, read, write, policy, sensitive.
 *             Pakai akun khusus, bukan admin utama. Port 8728 (8729 untuk TLS).
 * 'subnet'    awalan subnet VPN dengan titik di belakang, mis. '11.11.11.'
 * 'ip_start'/'ip_end' rentang IP statis yang dibagikan ke pelanggan
 *
 * Harga ada di 'plans' (per paket, Rupiah per bulan), bukan per server.
 */
return [
    'servers' => [
        'idn1' => [
            'name' => 'Indonesia 1',
            'host' => env('VPN_HOST_IDN1', 'routerverse.web.id'),
            'public_ip' => env('VPN_PUBLIC_IP_IDN1', ''),
            'subnet' => env('VPN_SUBNET_IDN1', '11.11.11.'),
            'ip_start' => (int) env('VPN_IP_START_IDN1', 10),
            'ip_end' => (int) env('VPN_IP_END_IDN1', 250),
            'profile' => env('VPN_PROFILE_IDN1', 'default'),
            'api' => [
                'host' => env('VPN_API_HOST_IDN1', ''),
                'port' => (int) env('VPN_API_PORT_IDN1', 8728),
                'user' => env('VPN_API_USER_IDN1', ''),
                'password' => env('VPN_API_PASSWORD_IDN1', ''),
                'ssl' => (bool) env('VPN_API_SSL_IDN1', false),
            ],
        ],
        'idn2' => [
            'name' => 'Indonesia 2',
            'host' => env('VPN_HOST_IDN2', 'vpn2.routerverse.web.id'),
            'public_ip' => env('VPN_PUBLIC_IP_IDN2', ''),
            'subnet' => env('VPN_SUBNET_IDN2', '12.12.12.'),
            'ip_start' => (int) env('VPN_IP_START_IDN2', 10),
            'ip_end' => (int) env('VPN_IP_END_IDN2', 250),
            'profile' => env('VPN_PROFILE_IDN2', 'default'),
            'api' => [
                'host' => env('VPN_API_HOST_IDN2', ''),
                'port' => (int) env('VPN_API_PORT_IDN2', 8728),
                'user' => env('VPN_API_USER_IDN2', ''),
                'password' => env('VPN_API_PASSWORD_IDN2', ''),
                'ssl' => (bool) env('VPN_API_SSL_IDN2', false),
            ],
        ],
    ],

    // Paket. 'fixed' = daftar port yang langsung didapat; null = pelanggan memilih satu port dari 'choose'.
    'plans' => [
        'port1' => ['name' => '1 Port', 'price' => 2000, 'desc' => 'Pilih satu: Winbox, API, atau port custom.', 'fixed' => null, 'choose' => ['winbox', 'api', 'custom']],
        'port4' => ['name' => '4 Port', 'price' => 4000, 'desc' => 'Winbox, API, WebFig, dan HTTPS sekaligus.', 'fixed' => ['winbox', 'api', 'webfig', 'https'], 'choose' => []],
    ],

    // Port tujuan yang umum dipakai di MikroTik.
    'ports' => [
        'winbox' => ['label' => 'Winbox', 'port' => 8291],
        'api'    => ['label' => 'API',    'port' => 8728],
        'webfig' => ['label' => 'WebFig', 'port' => 80],
        'https'  => ['label' => 'HTTPS',  'port' => 443],
        'custom' => ['label' => 'Custom', 'port' => null],
    ],

    // Rentang port publik (sisi server) yang dibagikan otomatis saat akun diaktifkan.
    // Paket 4 port mendapat 4 nomor berurutan.
    'remote_port_min' => (int) env('VPN_REMOTE_PORT_MIN', 20000),
    'remote_port_max' => (int) env('VPN_REMOTE_PORT_MAX', 29999),

    // Watchdog di script client: cek tunnel tiap N detik dan sambungkan ulang. 0 = tanpa watchdog.
    'watchdog_seconds' => (int) env('VPN_WATCHDOG_SECONDS', 60),
];
