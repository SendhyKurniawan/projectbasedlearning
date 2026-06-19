<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel courses (mata kuliah). Catatan: keunikan kode_matkul di sini kelak DILONGGARKAN
// oleh migrasi relax_course_kode_matkul_unique agar mendukung satu matkul untuk banyak kelas.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_matkul')->unique(); // kode matkul (unik untuk sementara, dilonggarkan kemudian)
            $table->string('nama_matkul');
            $table->integer('sks')->default(3);
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained()->onDelete('set null');
            $table->string('course_img')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
