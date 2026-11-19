<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_accounts', function (Blueprint $table) {
            $table->string('plan', 12)->default('port1')->after('server'); // port1 | port4
        });

        Schema::create('vpn_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vpn_account_id')->constrained('vpn_accounts')->cascadeOnDelete();
            $table->string('server', 20);
            $table->string('name', 12); // winbox | api | webfig | https | custom
            $table->unsignedInteger('public_port')->nullable(); // dibagikan saat akun aktif
            $table->unsignedInteger('target_port');
            $table->timestamps();

            $table->unique(['server', 'public_port']);
        });

        // Akun lama (satu port) dipindahkan ke tabel port.
        foreach (DB::table('vpn_accounts')->get() as $a) {
            DB::table('vpn_ports')->insert([
                'vpn_account_id' => $a->id, 'server' => $a->server, 'name' => $a->port_type,
                'public_port' => $a->remote_port, 'target_port' => $a->target_port,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_ports');
        Schema::table('vpn_accounts', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
