<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cache saldo. Sumber kebenaran: jumlah point_ledgers.amount.
            $table->unsignedBigInteger('points')->default(0)->after('role');
        });

        Schema::create('point_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12); // topup | purchase | refund | adjust
            $table->bigInteger('amount'); // + masuk, - keluar (1 poin = Rp 1)
            $table->unsignedBigInteger('balance_after');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable()->after('price_label'); // Rupiah; null = harga ditentukan admin
            $table->string('paid_with', 10)->nullable()->after('status'); // points | manual
            $table->timestamp('paid_at')->nullable()->after('paid_with');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['price', 'paid_with', 'paid_at']);
        });
        Schema::dropIfExists('point_ledgers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
