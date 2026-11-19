<?php

namespace App\Console\Commands;

use App\Models\VpnAccount;
use App\Services\VpnProvisioner;
use Illuminate\Console\Command;

class VpnTest extends Command
{
    protected $signature = 'vpn:test';

    protected $description = 'Cek koneksi RouterOS API tiap server VPN dan tampilkan akun yang belum tersinkron beserta alasannya';

    public function handle(): int
    {
        $bad = false;
        foreach (config('vpn.servers') as $key => $srv) {
            $this->line("== {$key} ({$srv['name']}) ==");
            $a = $srv['api'];
            if (empty($a['host']) || empty($a['user'])) {
                $this->error('  VPN_API_HOST_'.strtoupper($key).' / VPN_API_USER_'.strtoupper($key).' belum diisi di .env (bukan MIKROTIK_*).');
                $bad = true;

                continue;
            }
            $this->line("  API  : {$a['host']}:{$a['port']}".($a['ssl'] ? ' (TLS)' : '').", user {$a['user']}");
            if (! str_ends_with($srv['subnet'], '.')) {
                $this->warn("  Subnet '{$srv['subnet']}' harus diakhiri titik, mis. 10.20.30.");
            }
            try {
                $profiles = VpnProvisioner::make()->test($key);
                $this->info('  Koneksi dan login: OK');
                if (in_array($srv['profile'], $profiles, true)) {
                    $this->info("  Profil PPP '{$srv['profile']}': ada");
                } else {
                    $this->error("  Profil PPP '{$srv['profile']}' TIDAK ADA di MikroTik. Yang tersedia: ".implode(', ', $profiles).'. Atur VPN_PROFILE_'.strtoupper($key).'.');
                    $bad = true;
                }
            } catch (\Throwable $e) {
                $this->error('  GAGAL: '.$e->getMessage());
                $bad = true;
            }
        }

        $this->line('');
        $this->line('== Akun ==');
        $count = VpnAccount::count();
        $this->line("  Total {$count}, menunggu pembayaran/konfirmasi: ".VpnAccount::where('status', 'pending')->count());
        foreach (VpnAccount::whereIn('status', ['active', 'expired'])->where(fn ($q) => $q->where('needs_sync', true)->orWhereNotNull('sync_error'))->get() as $v) {
            $this->warn("  {$v->username} ({$v->server}) belum tersinkron: ".($v->sync_error ?: 'menunggu antrean atau vpn:sync'));
            $bad = true;
        }
        $this->line("  Antrean: koneksi '".config('queue.default')."'. Pastikan 'php artisan queue:work' berjalan, atau jalankan 'php artisan vpn:sync'.");

        return $bad ? self::FAILURE : self::SUCCESS;
    }
}
