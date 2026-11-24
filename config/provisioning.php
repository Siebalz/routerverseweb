<?php

return [
    // --- MikroTik CHR (VPN server) ---
    'mikrotik' => [
        'host' => env('MIKROTIK_HOST', '192.168.1.10'), // IP lokal CHR
        'rest_port' => env('MIKROTIK_REST_PORT', 443),
        'user' => env('MIKROTIK_USER', 'admin'),
        'password' => env('MIKROTIK_PASSWORD', ''),
        'use_ssl' => env('MIKROTIK_USE_SSL', true),
        'ppp_profile' => env('MIKROTIK_PPP_PROFILE', 'vpn-profile'),
        'vpn_ip_base' => env('MIKROTIK_VPN_IP_BASE', '10.20.0.'), // + nomor urut
        'vpn_ip_start' => (int) env('MIKROTIK_VPN_IP_START', 10),
    ],

    // --- Docker host (Mikhmon per pelanggan), diakses lewat API Portainer ---
    'docker' => [
        // Portainer meneruskan Docker Engine API di /api/endpoints/{id}/docker.
        // Isi DOCKER_API_KEY dengan access token Portainer (diawali ptr_).
        'api_url' => env('DOCKER_API_URL', 'http://192.168.5.45:9000/api/endpoints/3/docker'),
        'api_key' => env('DOCKER_API_KEY'),
        'verify' => (bool) env('DOCKER_API_VERIFY', false),
        'mikhmon_image' => env('MIKHMON_IMAGE', 'animegasan/mikhmon:v3-latest'),
        'network' => env('DOCKER_NETWORK', 'mikhmon-net'),
        'base_domain' => env('MIKHMON_BASE_DOMAIN', 'mikhmonverse.my.id'),
        'memory_mb' => (int) env('MIKHMON_MEMORY_MB', 128),
        'price' => (int) env('MIKHMON_PRICE', 10000),
        // Kotak login Traefik di depan Mikhmon. Mati = langsung ke halaman login Mikhmon.
        'gate' => (bool) env('MIKHMON_GATE', false),
    ],
];