<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // --- VPN (L2TP/IPsec via MikroTik CHR) ---
            $table->string('vpn_username')->nullable();
            $table->string('vpn_password')->nullable();
            $table->string('vpn_ip')->nullable(); // IP tetap dari pool CHR

            // --- Mikhmon (Docker + Traefik) ---
            $table->string('docker_container_id')->nullable();
            $table->string('docker_container_name')->nullable();
            $table->string('mikhmon_subdomain')->nullable();
            $table->integer('docker_internal_port')->nullable();

            // --- Status provisioning ---
            $table->enum('status', [
                'pending',       // belum diprovisioning
                'provisioning',  // sedang diproses
                'active',
                'failed',
                'suspended',
                'terminated',
            ])->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('provisioned_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_instances');
    }
};
