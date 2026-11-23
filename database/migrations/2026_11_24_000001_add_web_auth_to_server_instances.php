<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_instances', function (Blueprint $table) {
            if (! Schema::hasColumn('server_instances', 'web_user')) {
                $table->string('web_user')->nullable();
            }
            if (! Schema::hasColumn('server_instances', 'web_password')) {
                $table->text('web_password')->nullable(); // terenkripsi lewat cast model
            }
        });
    }

    public function down(): void
    {
        Schema::table('server_instances', function (Blueprint $table) {
            foreach (['web_user', 'web_password'] as $c) {
                if (Schema::hasColumn('server_instances', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
