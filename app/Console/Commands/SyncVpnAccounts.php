<?php

namespace App\Console\Commands;

use App\Jobs\SyncVpnAccountJob;
use App\Models\VpnAccount;
use Illuminate\Console\Command;

class SyncVpnAccounts extends Command
{
    protected $signature = 'vpn:sync';

    protected $description = 'Nonaktifkan akun VPN yang habis masa aktif dan terapkan perubahan yang tertunda ke MikroTik';

    public function handle(): int
    {
        // Akun aktif yang sudah lewat tanggal: tandai expired supaya secret-nya dinonaktifkan.
        VpnAccount::where('status', 'active')->whereNotNull('expired_at')->where('expired_at', '<=', now())
            ->get()->each(fn ($v) => $v->update(['status' => 'expired', 'needs_sync' => true]));

        $ok = $fail = 0;
        foreach (VpnAccount::whereIn('status', ['active', 'expired'])->where('needs_sync', true)->get() as $vpn) {
            try {
                (new SyncVpnAccountJob($vpn->id))->apply($vpn);
                $ok++;
                $this->line("OK   {$vpn->username}");
            } catch (\Throwable $e) {
                $vpn->update(['sync_error' => mb_substr($e->getMessage(), 0, 500)]);
                $fail++;
                $this->error("GAGAL {$vpn->username}: {$e->getMessage()}");
            }
        }
        $this->info("Selesai. {$ok} akun diterapkan, {$fail} gagal.");

        return $fail ? self::FAILURE : self::SUCCESS;
    }
}
