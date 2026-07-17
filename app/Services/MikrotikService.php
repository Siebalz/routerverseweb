<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client sederhana untuk RouterOS 7 REST API.
 * Docs: https://help.mikrotik.com/docs/spaces/ROS/pages/47579162/REST+API
 */
class MikrotikService
{
    protected string $baseUrl;
    protected string $user;
    protected string $password;

    public function __construct()
    {
        $cfg = config('provisioning.mikrotik');
        $scheme = $cfg['use_ssl'] ? 'https' : 'http';
        $this->baseUrl = "{$scheme}://{$cfg['host']}:{$cfg['rest_port']}/rest";
        $this->user = $cfg['user'];
        $this->password = $cfg['password'];
    }

    protected function client()
    {
        // withoutVerifying() dipakai karena CHR biasanya pakai self-signed cert.
        // Kalau sudah pasang certificate valid, hapus baris ini.
        return Http::withBasicAuth($this->user, $this->password)
            ->withoutVerifying()
            ->acceptJson();
    }

    /**
     * Bikin PPP secret baru untuk customer (dipakai login L2TP).
     */
    public function createPppSecret(string $username, string $password, string $localAddress, string $remoteAddress): array
    {
        $cfg = config('provisioning.mikrotik');

        $response = $this->client()->post("{$this->baseUrl}/ppp/secret", [
            'name' => $username,
            'password' => $password,
            'service' => 'l2tp',
            'profile' => $cfg['ppp_profile'],
            'local-address' => $localAddress,
            'remote-address' => $remoteAddress,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat PPP secret di CHR: '.$response->body());
        }

        return $response->json();
    }

    public function deletePppSecret(string $username): void
    {
        $existing = $this->client()->get("{$this->baseUrl}/ppp/secret", ['name' => $username]);
        foreach ($existing->json() ?? [] as $secret) {
            if (($secret['name'] ?? null) === $username) {
                $this->client()->delete("{$this->baseUrl}/ppp/secret/{$secret['.id']}");
            }
        }
    }

    /**
     * Cari IP berikutnya yang belum dipakai dari pool VPN.
     * Sederhana: increment counter berbasis jumlah secret yang sudah ada.
     */
    public function nextAvailableVpnIp(): string
    {
        $cfg = config('provisioning.mikrotik');
        $response = $this->client()->get("{$this->baseUrl}/ppp/secret");
        $used = collect($response->json() ?? [])
            ->pluck('remote-address')
            ->filter()
            ->map(fn ($ip) => (int) substr($ip, strrpos($ip, '.') + 1));

        $candidate = $cfg['vpn_ip_start'];
        while ($used->contains($candidate)) {
            $candidate++;
        }

        return $cfg['vpn_ip_base'].$candidate;
    }
}
