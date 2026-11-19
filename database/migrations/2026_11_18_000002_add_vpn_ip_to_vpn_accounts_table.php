<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_accounts', function (Blueprint $table) {
            // IP statis di dalam VPN yang dibagikan server saat akun dibuat.
            $table->string('vpn_ip', 45)->nullable()->after('remote_port');
            $table->text('sync_error')->nullable()->after('needs_sync');
        });
    }

    public function down(): void
    {
        Schema::table('vpn_accounts', function (Blueprint $table) {
            $table->dropColumn(['vpn_ip', 'sync_error']);
        });
    }
};
