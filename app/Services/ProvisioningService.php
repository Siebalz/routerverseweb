<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ServerInstance;
use Throwable;

/**
 * Menyiapkan Mikhmon Online untuk satu pesanan: satu container di Docker host.
 * Akun VPN pelanggan diurus terpisah oleh VpnAccount, bukan di sini.
 */
class ProvisioningService
{
    public function __construct(protected DockerProvisioningService $docker)
    {
    }

    public function provision(Order $order): ServerInstance
    {
        $instance = ServerInstance::firstOrCreate(['order_id' => $order->id], ['status' => 'pending']);

        // Sudah berjalan: jangan dibuat ulang (data Mikhmon pelanggan ikut hilang bila container diganti).
        if ($instance->status === 'active') {
            return $instance;
        }

        // Dinonaktifkan karena habis masa aktif lalu diperpanjang: cukup dinyalakan lagi.
        if ($instance->status === 'suspended' && $instance->docker_container_name) {
            $this->docker->start($instance->docker_container_name);
            $instance->update(['status' => 'active', 'last_error' => null]);

            return $instance;
        }

        $instance->update(['status' => 'provisioning', 'last_error' => null]);

        try {
            $result = $this->docker->provisionMikhmon($order->id, $instance->mikhmon_subdomain, $instance->web_password);

            $instance->update([
                'docker_container_id' => $result['container_id'],
                'docker_container_name' => $result['container_name'],
                'mikhmon_subdomain' => $result['subdomain'],
                'web_user' => $result['web_user'],
                'web_password' => $result['web_password'],
                'status' => 'active',
                'provisioned_at' => now(),
            ]);
        } catch (Throwable $e) {
            $instance->update(['status' => 'failed', 'last_error' => $e->getMessage()]);
            throw $e;
        }

        return $instance;
    }

    public function terminate(ServerInstance $instance): void
    {
        if ($instance->docker_container_name) {
            $this->docker->removeContainer($instance->docker_container_name);
        }
        $instance->update(['status' => 'terminated']);
    }
}
