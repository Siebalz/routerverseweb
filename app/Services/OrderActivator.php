<?php

namespace App\Services;

use App\Jobs\ProvisionOrderJob;
use App\Models\Order;
use App\Models\VpnAccount;

/**
 * Satu-satunya jalur mengaktifkan pesanan. Dipakai admin (ubah status), bayar poin, dan
 * nanti callback gateway, supaya perilakunya selalu sama.
 */
class OrderActivator
{
    /**
     * @return string|null pesan galat sinkron VPN ke MikroTik, atau null bila beres
     */
    public function activate(Order $order, string $status = 'diproses'): ?string
    {
        $order->status = $status;
        $order->activateForOneMonth();
        $order->save();

        $error = $this->applyVpn($order);

        // Provisioning container Mikhmon lewat antrean (butuh `php artisan queue:work`); idempotent di ProvisioningService.
        if ($status === 'diproses' && $order->category === 'hosting' && str_contains(strtolower($order->service_name), 'mikhmon')) {
            ProvisionOrderJob::dispatch($order);
        }

        return $error;
    }

    /** Terapkan akun VPN milik pesanan ini ke MikroTik. Return galat pertama, atau null. */
    public function applyVpn(Order $order): ?string
    {
        $error = null;
        foreach (VpnAccount::where('order_id', $order->id)->get() as $vpn) {
            $vpn->applyOrder($order);
            $error ??= $vpn->fresh()->sync_error;
        }

        return $error;
    }
}
