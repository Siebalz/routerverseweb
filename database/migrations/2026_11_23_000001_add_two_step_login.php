<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aman dijalankan ulang: MySQL tidak membatalkan perubahan tabel (DDL) bila migrasi gagal di tengah,
     * jadi setiap langkah dicek dulu.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'otp_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                // Pelanggan yang memilih diminta kode OTP saat login. Admin selalu diminta (diatur di config/security.php).
                $table->boolean('otp_enabled')->default(false);
            });
        }

        // Akun yang sudah ada sebelum fitur ini tidak boleh terkunci: anggap emailnya terverifikasi.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);

        // Satu kode aktif per pengguna. Yang disimpan hanya hash, bukan kodenya.
        if (! Schema::hasTable('login_otps')) {
            Schema::create('login_otps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('code_hash', 64);
                $table->dateTime('expires_at');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->dateTime('sent_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');

        if (Schema::hasColumn('users', 'otp_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('otp_enabled');
            });
        }
    }
};
