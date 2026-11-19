<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vpn_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Pesanan yang sedang menunggu diaktifkan (pesanan awal atau perpanjangan).
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            // Pesanan terakhir yang sudah diterapkan, supaya simpan status dua kali tidak memperpanjang dua kali.
            $table->unsignedBigInteger('applied_order_id')->nullable();

            $table->string('server', 20);
            $table->string('username', 24)->unique();
            $table->text('password'); // terenkripsi lewat cast 'encrypted'
            $table->string('port_type', 10)->default('winbox'); // winbox | api | custom
            $table->unsignedInteger('target_port')->default(8291);
            $table->unsignedInteger('remote_port')->nullable(); // dibagikan saat aktif

            $table->unsignedInteger('price')->default(0);
            $table->string('status', 12)->default('pending'); // pending | active | expired
            $table->boolean('needs_sync')->default(false); // ubah port/password menunggu diterapkan ke server
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->unique(['server', 'remote_port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_accounts');
    }
};
