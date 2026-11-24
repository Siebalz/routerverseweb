<?php

namespace App\Console\Commands;

use App\Models\ServerInstance;
use App\Services\DockerProvisioningService;
use Illuminate\Console\Command;
use Throwable;

class SyncMikhmon extends Command
{
    protected $signature = 'mikhmon:sync';

    protected $description = 'Hentikan container Mikhmon yang masa aktifnya habis, nyalakan lagi bila sudah diperpanjang';

    public function handle(DockerProvisioningService $docker): int
    {
        $failed = 0;

        ServerInstance::with('order')->whereIn('status', ['active', 'suspended'])
            ->whereNotNull('docker_container_name')->get()
            ->each(function (ServerInstance $i) use ($docker, &$failed) {
                $o = $i->order;
                // Tidak boleh jalan bila pesanan tidak aktif (expired, dibatalkan, pending) atau masa aktifnya lewat.
                $expired = ! $o
                    || ! in_array($o->status, \App\Models\Order::ACTIVE_STATUSES, true)
                    || ($o->expired_at && $o->expired_at->isPast());

                try {
                    if ($expired && $i->status === 'active') {
                        $docker->stop($i->docker_container_name);
                        $i->update(['status' => 'suspended']);
                        $this->line("Dihentikan: {$i->docker_container_name}");
                    } elseif (! $expired && $i->status === 'suspended') {
                        $docker->start($i->docker_container_name);
                        $i->update(['status' => 'active']);
                        $this->line("Dinyalakan: {$i->docker_container_name}");
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $i->update(['last_error' => $e->getMessage()]);
                    $this->error("{$i->docker_container_name}: {$e->getMessage()}");
                }
            });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
