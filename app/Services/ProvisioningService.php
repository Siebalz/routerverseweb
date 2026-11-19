<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ServerInstance;
use Illuminate\Support\Str;
use Throwable;

class ProvisioningService
{
    public function __construct(
        protected MikrotikService $mikrotik,
        protected DockerProvisioningService $docker,
    ) {
    }

    /**
     * Provisioning lengkap untuk 1 order: VPN secret di CHR + container Mikhmon.
     * Dipanggil saat admin ubah status order jadi "diproses".
     */
    public function provision(Order $order): ServerInstance
    {
        $instance = ServerInstance::firstOrCreate(
            ['order_id' => $order->id],
            ['status' => 'pending']
        );

        // Kalau sudah pernah aktif, jangan provisioning ulang.
        if ($instance->status === 'active') {
            return $instance;
        }

        $instance->update(['status' => 'provisioning', 'last_error' => null]);

        try {
            // 1. Buat kredensial VPN unik untuk customer
            $username = 'cust'.$order->id.'_'.Str::lower(Str::random(4));
            $password = Str::random(12);
            $vpnIp = $this->mikrotik->nextAvailableVpnIp();

            $this->mikrotik->createPppSecret(
                username: $username,
                password: $password,
                localAddress: config('provisioning.mikrotik.vpn_ip_base').'1',
                remoteAddress: $vpnIp,
            );

            // 2. Buat container Mikhmon yang menunjuk ke IP VPN customer tsb
            $docker = $this->docker->provisionMikhmon($order->id, $vpnIp);

            // 3. Simpan hasil
            $instance->update([
                'vpn_username' => $username,
                'vpn_password' => $password,
                'vpn_ip' => $vpnIp,
                'docker_container_id' => $docker['container_id'],
                'docker_container_name' => $docker['container_name'],
                'mikhmon_subdomain' => $docker['subdomain'],
                'status' => 'active',
                'provisioned_at' => now(),
            ]);
        } catch (Throwable $e) {
            $instance->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $instance;
    }

    public function terminate(ServerInstance $instance): void
    {
        if ($instance->docker_container_id) {
            $this->docker->removeContainer($instance->docker_container_id);
        }
        if ($instance->vpn_username) {
            $this->mikrotik->deletePppSecret($instance->vpn_username);
        }
        $instance->update(['status' => 'terminated']);
    }
}
