<?php

namespace App\Services;

use RuntimeException;

/**
 * Membuat, mengubah, menonaktifkan, dan menghapus akun VPN L2TP di MikroTik server lewat RouterOS API.
 * Algoritma dari pantau/app/vpn.py:
 *   1. PPP secret (service=l2tp) dengan IP statis dari subnet VPN yang masih kosong
 *   2. NAT dst-nat: port publik server -> IP VPN pelanggan : port tujuan (Winbox/API/custom)
 *   3. Script client L2TP siap tempel untuk router pelanggan
 * Semua NAT milik akun diberi comment "VPN-<username>-remote" supaya mudah dicari dan dibersihkan.
 */
class VpnProvisioner
{
    public const PREFIX = 'VPN-';

    /** @param array<string,array> $servers biasanya config('vpn.servers') */
    public function __construct(protected array $servers)
    {
    }

    public static function make(): self
    {
        return new self(config('vpn.servers'));
    }

    protected function server(string $key): array
    {
        $srv = $this->servers[$key] ?? null;
        if (! $srv || empty($srv['api']['host'])) {
            throw new RuntimeException("API MikroTik untuk server '{$key}' belum diisi di config/vpn.php atau .env.");
        }

        return $srv;
    }

    protected function open(array $srv): RouterOsApi
    {
        $a = $srv['api'];

        return RouterOsApi::connect($a['host'], (int) $a['port'], (bool) $a['ssl'], $a['user'], (string) $a['password']);
    }

    public static function natComment(string $username, string $name): string
    {
        return self::PREFIX.$username.'-'.$name;
    }

    /**
     * Samakan keadaan router dengan data akun. Aman dipanggil berulang.
     *
     * @param  array<int,array{name:string,public:int,target:int}>  $ports  satu aturan NAT per port
     * @return string IP VPN statis akun ini (simpan di database)
     */
    public function apply(string $serverKey, string $username, string $password, array $ports, bool $enabled, ?string $knownIp = null): string
    {
        $srv = $this->server($serverKey);
        $c = $this->open($srv);
        try {
            $this->assertPortsFree($c, $username, $ports); // sebelum menyentuh secret, supaya tidak setengah jadi
            [$sec] = $c->call('/ppp/secret/print');
            $mine = null;
            foreach ($sec as $s) {
                if (($s['name'] ?? '') === $username) {
                    $mine = $s;
                }
            }

            if ($mine) {
                $ip = $mine['remote-address'] ?? '' ?: ($knownIp ?: $this->freeIp($srv, $sec));
                $c->call('/ppp/secret/set', [
                    '.id' => $mine['.id'], 'password' => $password, 'profile' => $srv['profile'],
                    'remote-address' => $ip, 'disabled' => $enabled ? 'no' : 'yes',
                ]);
            } else {
                $ip = $knownIp && ! $this->ipUsed($sec, $knownIp) ? $knownIp : $this->freeIp($srv, $sec);
                $c->call('/ppp/secret/add', [
                    'name' => $username, 'password' => $password, 'profile' => $srv['profile'],
                    'service' => 'l2tp', 'remote-address' => $ip, 'disabled' => $enabled ? 'no' : 'yes',
                ]);
            }

            try {
                $this->applyNat($c, $srv, $username, $ip, $ports);
            } catch (\Throwable $e) {
                if (! $mine) { // akun baru: jangan tinggalkan secret tanpa NAT
                    foreach ($c->call('/ppp/secret/print', [], ['?name='.$username])[0] as $s) {
                        $c->call('/ppp/secret/remove', ['.id' => $s['.id']]);
                    }
                }
                throw $e;
            }

            if (! $enabled) {
                $this->kick($c, $username);
            }

            return $ip;
        } finally {
            $c->close();
        }
    }

    /** Nonaktifkan akun (masa aktif habis) dan putuskan sesi yang masih tersambung. */
    public function disable(string $serverKey, string $username): void
    {
        $c = $this->open($this->server($serverKey));
        try {
            [$sec] = $c->call('/ppp/secret/print', [], ['?name='.$username]);
            foreach ($sec as $s) {
                $c->call('/ppp/secret/disable', ['.id' => $s['.id']]);
            }
            $this->kick($c, $username);
        } finally {
            $c->close();
        }
    }

    /** Hapus secret, sesi aktif, dan semua NAT milik akun. */
    public function remove(string $serverKey, string $username): int
    {
        $c = $this->open($this->server($serverKey));
        try {
            [$sec] = $c->call('/ppp/secret/print', [], ['?name='.$username]);
            foreach ($sec as $s) {
                $c->call('/ppp/secret/remove', ['.id' => $s['.id']]);
            }
            $this->kick($c, $username);
            $removed = 0;
            [$nat] = $c->call('/ip/firewall/nat/print', [], [], ['.id', 'comment']);
            foreach ($nat as $r) {
                if (str_starts_with($r['comment'] ?? '', self::PREFIX.$username.'-')) {
                    $c->call('/ip/firewall/nat/remove', ['.id' => $r['.id']]);
                    $removed++;
                }
            }

            return $removed;
        } finally {
            $c->close();
        }
    }

    /** Tes koneksi + login ke API server. Return nama profil PPP yang tersedia. */
    public function test(string $serverKey): array
    {
        $c = $this->open($this->server($serverKey));
        try {
            [$p] = $c->call('/ppp/profile/print', [], [], ['name']);

            return array_column($p, 'name');
        } finally {
            $c->close();
        }
    }

    // ---------------------------------------------------------------- internal
    /** Gagal lebih awal bila ada port publik yang sudah dipakai aturan NAT milik akun lain. */
    protected function assertPortsFree(RouterOsApi $c, string $username, array $ports): void
    {
        [$nat] = $c->call('/ip/firewall/nat/print', [], [], ['comment', 'dst-port']);
        $mine = self::PREFIX.$username.'-';
        foreach ($nat as $r) {
            if (str_starts_with($r['comment'] ?? '', $mine)) {
                continue;
            }
            $used = array_map('trim', explode(',', $r['dst-port'] ?? ''));
            foreach ($ports as $p) {
                if (in_array((string) $p['public'], $used, true)) {
                    throw new RuntimeException("Port publik {$p['public']} sudah dipakai aturan NAT lain di server.");
                }
            }
        }
    }

    protected function applyNat(RouterOsApi $c, array $srv, string $username, string $ip, array $ports): void
    {
        [$nat] = $c->call('/ip/firewall/nat/print', [], [], ['.id', 'comment', 'dst-port', 'to-addresses', 'to-ports']);
        $prefix = self::PREFIX.$username.'-';
        $existing = [];
        foreach ($nat as $r) {
            if (str_starts_with($r['comment'] ?? '', $prefix)) {
                $existing[$r['comment']][] = $r;
            }
        }

        $wanted = [];
        foreach ($ports as $p) {
            $comment = self::natComment($username, $p['name']);
            $wanted[$comment] = true;
            $want = [
                'chain' => 'dstnat', 'action' => 'dst-nat', 'protocol' => 'tcp',
                'dst-port' => (string) $p['public'], 'to-addresses' => $ip, 'to-ports' => (string) $p['target'],
                'comment' => $comment,
            ];
            if (! empty($srv['public_ip'])) {
                $want['dst-address'] = $srv['public_ip'];
            }

            $rows = $existing[$comment] ?? [];
            if (! $rows) {
                $c->call('/ip/firewall/nat/add', $want);

                continue;
            }
            $first = array_shift($rows);
            if (($first['dst-port'] ?? '') !== $want['dst-port'] || ($first['to-addresses'] ?? '') !== $ip || ($first['to-ports'] ?? '') !== $want['to-ports']) {
                $c->call('/ip/firewall/nat/set', ['.id' => $first['.id']] + $want);
            }
            foreach ($rows as $extra) { // duplikat
                $c->call('/ip/firewall/nat/remove', ['.id' => $extra['.id']]);
            }
        }

        // Aturan milik akun ini yang tidak lagi diinginkan (port dihapus, nama berubah).
        foreach ($existing as $comment => $rows) {
            if (! isset($wanted[$comment])) {
                foreach ($rows as $r) {
                    $c->call('/ip/firewall/nat/remove', ['.id' => $r['.id']]);
                }
            }
        }
    }

    protected function kick(RouterOsApi $c, string $username): void
    {
        [$act] = $c->call('/ppp/active/print', [], ['?name='.$username]);
        foreach ($act as $a) {
            try {
                $c->call('/ppp/active/remove', ['.id' => $a['.id']]);
            } catch (RouterOsException) {
            }
        }
    }

    protected function ipUsed(array $secrets, string $ip): bool
    {
        foreach ($secrets as $s) {
            if (($s['remote-address'] ?? '') === $ip) {
                return true;
            }
        }

        return false;
    }

    protected function freeIp(array $srv, array $secrets): string
    {
        $used = [];
        foreach ($secrets as $s) {
            if (! empty($s['remote-address'])) {
                $used[$s['remote-address']] = true;
            }
        }
        for ($i = (int) $srv['ip_start']; $i <= (int) $srv['ip_end']; $i++) {
            $ip = $srv['subnet'].$i;
            if (! isset($used[$ip])) {
                return $ip;
            }
        }
        throw new RuntimeException('Alokasi IP statis pada subnet VPN sudah habis. Perbesar ip_end di config/vpn.php.');
    }

    // ---------------------------------------------------------------- script client
    /** Escape string untuk literal "..." di script RouterOS. */
    public static function esc(string $s): string
    {
        return strtr($s, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$', '?' => '\\?']);
    }

    public static function connName(string $username): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $username).'-vpn';
    }

    /** Script untuk ditempel di terminal MikroTik pelanggan. */
    public function clientScript(string $serverKey, string $username, string $password, int $watchdogSeconds = 60): string
    {
        $srv = $this->servers[$serverKey];
        $conn = self::connName($username);
        $to = $srv['host']; // domain; DNS-nya mengarah ke IP publik server
        $lines = ["/interface l2tp-client add name={$conn} connect-to={$to} user=\"".self::esc($username).'" password="'.self::esc($password).'" disabled=no'];
        if ($watchdogSeconds > 0) {
            $lines[] = $this->watchdogScript($conn, max(10, $watchdogSeconds));
        }

        return implode("\n", $lines);
    }

    /** Cek tunnel tiap N detik; sambungkan ulang kalau putus atau macet (ping ke ujung tunnel gagal). */
    public function watchdogScript(string $conn, int $interval = 60): string
    {
        $src = implode("\n", [
            ":local c \"{$conn}\"",
            ':local i [/interface l2tp-client find name=$c]',
            ':if ([:len $i]=0) do={:error "vpn-watchdog: client $c tidak ada"}',
            ':local bad false',
            ':if ([/interface l2tp-client get $i disabled]) do={:set bad true}',
            ':if (!$bad) do={',
            '  :if (![/interface l2tp-client get $i running]) do={:set bad true} else={',
            '    :local gw ""',
            '    :do {:set gw [/ip address get [/ip address find interface=$c] network]} on-error={}',
            '    :if ($gw!="") do={:if ([/ping $gw count=3 interval=1s]=0) do={:set bad true}}',
            '  }',
            '}',
            ':if ($bad) do={',
            '  :log warning "vpn-watchdog: $c putus/macet, menyambung ulang"',
            '  /interface l2tp-client disable $i',
            '  :delay 3s',
            '  /interface l2tp-client enable $i',
            '}',
        ]);
        $src = implode('\\n', array_map([self::class, 'esc'], explode("\n", $src)));

        return implode("\n", [
            '# --- watchdog VPN: sambungkan ulang otomatis bila tunnel putus/macet ---',
            ':do { /system script remove [find name=vpn-watchdog] } on-error={}',
            ':do { /system scheduler remove [find name=vpn-watchdog] } on-error={}',
            "/system script add name=vpn-watchdog policy=read,write,test source=\"{$src}\"",
            '/system scheduler add name=vpn-watchdog interval='.(int) $interval.'s start-time=startup on-event=vpn-watchdog policy=read,write,test',
        ]);
    }
}
