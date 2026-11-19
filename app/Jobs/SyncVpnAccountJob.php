<?php

namespace App\Jobs;

use App\Models\VpnAccount;
use App\Services\VpnProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/** Terapkan data satu akun VPN ke MikroTik server (secret + NAT). Aman diulang. */
class SyncVpnAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $vpnId)
    {
    }

    public function handle(): void
    {
        $vpn = VpnAccount::find($this->vpnId);
        if (! $vpn || $vpn->status === 'pending' || ! $vpn->ports()->whereNotNull('public_port')->exists()) {
            return;
        }

        try {
            $this->apply($vpn);
        } catch (\Throwable $e) {
            $vpn->update(['sync_error' => mb_substr($e->getMessage(), 0, 500)]);
            Log::warning("Sinkron VPN {$vpn->username} gagal: ".$e->getMessage());
            throw $e; // biar dicoba ulang oleh antrean; perintah vpn:sync juga mengulang
        }
    }

    /** Dipakai juga oleh perintah vpn:sync (tanpa antrean). */
    public function apply(VpnAccount $vpn): void
    {
        $enabled = $vpn->status === 'active' && ! ($vpn->expired_at && $vpn->expired_at->isPast());
        $ports = $vpn->ports()->whereNotNull('public_port')->get()
            ->map(fn ($p) => ['name' => $p->name, 'public' => (int) $p->public_port, 'target' => (int) $p->target_port])->all();
        $ip = VpnProvisioner::make()->apply($vpn->server, $vpn->username, $vpn->password, $ports, $enabled, $vpn->vpn_ip);
        $vpn->update(['vpn_ip' => $ip, 'needs_sync' => false, 'sync_error' => null]);
    }
}
