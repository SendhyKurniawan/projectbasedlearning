<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel academic_years (tahun ajaran) — tingkat teratas hierarki waktu akademik.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year_start'); // tahun mulai, mis. "2025"
            $table->string('year_end');   // tahun selesai, mis. "2026"
            $table->boolean('is_active')->default(false); // hanya satu tahun ajaran yang aktif
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
