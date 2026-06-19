<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel material_views (catatan "materi sudah dibaca": 1 mahasiswa × 1 materi).
// Dipakai sebagai syarat membuka tugas prasyarat.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('material_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('viewed_at');

            // Pastikan satu catatan baca per mahasiswa per materi
            $table->unique(['material_id', 'student_id']);

            // Index agar query lebih cepat
            $table->index('student_id');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_views');
    }
};
