<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected Order $order)
    {
    }

    public function handle(ProvisioningService $provisioning): void
    {
        $provisioning->provision($this->order);

        // TODO: kirim notifikasi ke customer (email/WA) berisi subdomain Mikhmon
        // dan kredensial VPN, setelah provisioning sukses.
    }
}
