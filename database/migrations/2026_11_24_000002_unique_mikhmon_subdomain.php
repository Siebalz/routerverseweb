<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_instances', function (Blueprint $table) {
            $table->unique('mikhmon_subdomain', 'server_instances_subdomain_unique');
        });
    }

    public function down(): void
    {
        Schema::table('server_instances', function (Blueprint $table) {
            $table->dropUnique('server_instances_subdomain_unique');
        });
    }
};
