<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client sederhana ke Docker Engine API buat bikin container Mikhmon
 * per customer, sudah dilabeli buat Traefik reverse proxy (subdomain).
 *
 * Catatan: kalau DOCKER_HOST_URI pakai unix socket, pastikan PHP-FPM/queue
 * worker punya akses baca-tulis ke /var/run/docker.sock (biasanya lewat
 * grup "docker"), atau expose Docker API lewat TCP di jaringan lokal saja
 * (JANGAN expose ke publik tanpa TLS + auth).
 */
class DockerProvisioningService
{
    protected string $apiBase;

    public function __construct()
    {
        $host = config('provisioning.docker.host');

        // Kalau pakai unix socket, http client butuh dukungan curl unix socket.
        // Paling aman untuk awal: jalankan Docker API lewat TCP lokal (127.0.0.1:2375)
        // yang di-bind hanya ke localhost, lalu isi DOCKER_HOST_URI=http://127.0.0.1:2375
        $this->apiBase = rtrim($host, '/');
    }

    protected function client()
    {
        return Http::baseUrl($this->apiBase)->acceptJson();
    }

    /**
     * Bikin & jalankan container Mikhmon baru untuk 1 customer.
     * Return: ['container_id' => ..., 'container_name' => ..., 'subdomain' => ...]
     */
    public function provisionMikhmon(int $orderId, string $vpnTargetIp): array
    {
        $cfg = config('provisioning.docker');
        $containerName = "mikhmon-order-{$orderId}";
        $subdomain = "order{$orderId}.{$cfg['base_domain']}";

        $create = $this->client()->post("/containers/create", [
            'Image' => $cfg['mikhmon_image'],
            'name' => $containerName,
            'Env' => [
                "MIKROTIK_HOST={$vpnTargetIp}", // Mikhmon connect ke Mikrotik customer lewat IP VPN
            ],
            'HostConfig' => [
                'NetworkMode' => $cfg['network'],
                'RestartPolicy' => ['Name' => 'unless-stopped'],
            ],
            'Labels' => [
                'traefik.enable' => 'true',
                "traefik.http.routers.{$containerName}.rule" => "Host(`{$subdomain}`)",
                "traefik.http.routers.{$containerName}.entrypoints" => 'websecure',
                "traefik.http.routers.{$containerName}.tls.certresolver" => 'letsencrypt',
                "traefik.http.services.{$containerName}.loadbalancer.server.port" => '80',
            ],
        ]);

        if ($create->failed()) {
            throw new RuntimeException('Gagal membuat container Mikhmon: '.$create->body());
        }

        $containerId = $create->json('Id');

        $start = $this->client()->post("/containers/{$containerId}/start");
        if ($start->failed() && $start->status() !== 304) {
            throw new RuntimeException('Gagal start container Mikhmon: '.$start->body());
        }

        return [
            'container_id' => $containerId,
            'container_name' => $containerName,
            'subdomain' => $subdomain,
        ];
    }

    public function removeContainer(string $containerId): void
    {
        $this->client()->delete("/containers/{$containerId}", ['force' => true]);
    }
}
