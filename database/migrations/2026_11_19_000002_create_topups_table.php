<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount'); // poin yang masuk (Rupiah)
            $table->unsignedInteger('fee')->default(0); // biaya, ditanggung pembeli
            $table->unsignedInteger('total'); // yang dibayar = amount + fee
            $table->string('merchant_order_id', 50)->unique();
            $table->string('reference', 64)->nullable(); // dari Duitku
            $table->text('payment_url')->nullable();
            $table->string('status', 10)->default('pending'); // pending | paid | failed
            $table->string('payment_code', 10)->nullable(); // metode bayar dari callback
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('point_ledgers', function (Blueprint $table) {
            // Satu top up hanya bisa menambah poin satu kali (dijaga di database).
            $table->foreignId('topup_id')->nullable()->unique()->after('order_id')->constrained('topups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_ledgers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topup_id');
        });
        Schema::dropIfExists('topups');
    }
};
