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

    // --- Docker host (via Docker Engine API) ---
    'docker' => [
        // Contoh: unix:///var/run/docker.sock atau http://192.168.1.20:2375
        'host' => env('DOCKER_HOST_URI', 'unix:///var/run/docker.sock'),
        'mikhmon_image' => env('MIKHMON_IMAGE', 'boxbilling/mikhmon:latest'),
        'network' => env('DOCKER_NETWORK', 'traefik-public'),
        'base_domain' => env('MIKHMON_BASE_DOMAIN', 'panel.domainmu.com'),
    ],
];
