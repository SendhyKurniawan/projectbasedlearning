<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahkan kolom OTP ke users untuk verifikasi email saat registrasi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable()->after('password');            // kode OTP 6 digit
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');       // batas waktu kode berlaku
            $table->timestamp('otp_verified_at')->nullable()->after('otp_expires_at'); // waktu OTP diverifikasi
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'otp_expires_at', 'otp_verified_at']);
        });
    }
};
