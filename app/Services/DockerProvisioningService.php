<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Membuat satu container Mikhmon per pelanggan di Docker host (192.168.5.45).
 *
 * Laravel tidak menyentuh docker.sock. Permintaan dikirim ke API Portainer
 * (port 4500) yang meneruskannya ke Docker Engine, memakai access token.
 * Container dipasangi label Traefik; Traefik di host yang sama mengarahkan
 * orderN.mikhmonverse.my.id ke container itu. HTTPS diurus Cloudflare Tunnel.
 */
class DockerProvisioningService
{
    public const WEB_USER = 'pelanggan';

    protected function client(int $timeout = 30): PendingRequest
    {
        $cfg = config('provisioning.docker');

        $http = Http::baseUrl(rtrim((string) $cfg['api_url'], '/'))->acceptJson()->timeout($timeout);
        if (! empty($cfg['api_key'])) {
            $http = $http->withHeaders(['X-API-Key' => $cfg['api_key']]);
        }
        if (! $cfg['verify']) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }

    public static function containerName(int $orderId): string
    {
        return "mikhmon-order-{$orderId}";
    }

    /** Tarik image bila belum ada di host. */
    protected function ensureImage(string $image): void
    {
        $exists = $this->client()->get('/images/'.$image.'/json');
        if ($exists->successful()) {
            return;
        }

        $lastColon = strrpos($image, ':');
        $hasTag = $lastColon !== false && $lastColon > strrpos($image, '/');
        $name = $hasTag ? substr($image, 0, $lastColon) : $image;
        $tag = $hasTag ? substr($image, $lastColon + 1) : 'latest';

        $pull = $this->client(300)->post('/images/create?'.http_build_query(['fromImage' => $name, 'tag' => $tag]));
        if ($pull->failed()) {
            throw new RuntimeException('Gagal menarik image '.$image.': '.Str::limit($pull->body(), 300));
        }
    }

    /**
     * Buat dan jalankan container Mikhmon untuk 1 pesanan.
     * Return: container_id, container_name, subdomain, web_user, web_password.
     */
    public function provisionMikhmon(int $orderId): array
    {
        $cfg = config('provisioning.docker');
        $name = self::containerName($orderId);
        $subdomain = "order{$orderId}.{$cfg['base_domain']}";

        // Pintu pertama: Mikhmon bawaan memakai login mikhmon/1234 yang sudah diketahui umum,
        // jadi alamatnya dikunci dulu dengan login dari Traefik sampai pelanggan menggantinya.
        $webPassword = Str::password(14, symbols: false);
        $hash = password_hash($webPassword, PASSWORD_BCRYPT);

        $this->ensureImage($cfg['mikhmon_image']);

        // Pengulangan job: buang sisa container lama dengan nama yang sama.
        $this->client()->delete('/containers/'.$name.'?force=true');

        $create = $this->client()->post('/containers/create?'.http_build_query(['name' => $name]), [
            'Image' => $cfg['mikhmon_image'],
            'HostConfig' => [
                'NetworkMode' => $cfg['network'],
                'RestartPolicy' => ['Name' => 'unless-stopped'],
                'Memory' => max(64, (int) $cfg['memory_mb']) * 1024 * 1024,
            ],
            'Labels' => [
                'traefik.enable' => 'true',
                'traefik.docker.network' => $cfg['network'],
                "traefik.http.routers.{$name}.rule" => "Host(`{$subdomain}`)",
                "traefik.http.routers.{$name}.entrypoints" => 'web',
                "traefik.http.routers.{$name}.middlewares" => "{$name}-auth",
                "traefik.http.middlewares.{$name}-auth.basicauth.users" => self::WEB_USER.':'.$hash,
                "traefik.http.services.{$name}.loadbalancer.server.port" => '80',
                'routerverse.order' => (string) $orderId,
            ],
        ]);

        if ($create->failed()) {
            throw new RuntimeException('Gagal membuat container Mikhmon: '.Str::limit($create->body(), 300));
        }

        $containerId = $create->json('Id');

        $start = $this->client()->post("/containers/{$containerId}/start");
        if ($start->failed() && $start->status() !== 304) {
            throw new RuntimeException('Gagal menjalankan container Mikhmon: '.Str::limit($start->body(), 300));
        }

        return [
            'container_id' => $containerId,
            'container_name' => $name,
            'subdomain' => $subdomain,
            'web_user' => self::WEB_USER,
            'web_password' => $webPassword,
        ];
    }

    /** Hentikan container (masa aktif habis). Data di dalamnya tetap ada. */
    public function stop(string $container): void
    {
        $r = $this->client()->post("/containers/{$container}/stop");
        if ($r->failed() && ! in_array($r->status(), [304, 404], true)) {
            throw new RuntimeException('Gagal menghentikan container: '.Str::limit($r->body(), 300));
        }
    }

    public function start(string $container): void
    {
        $r = $this->client()->post("/containers/{$container}/start");
        if ($r->failed() && $r->status() !== 304) {
            throw new RuntimeException('Gagal menjalankan container: '.Str::limit($r->body(), 300));
        }
    }

    public function removeContainer(string $container): void
    {
        $this->client()->delete("/containers/{$container}?force=true");
    }
}
