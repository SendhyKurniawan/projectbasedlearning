<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel pivot enrollments (relasi banyak-ke-banyak mahasiswa ↔ course),
// menyimpan nilai akhir & waktu pendaftaran. Flow mahasiswa sering query tabel ini langsung.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();
            
            // Cegah pendaftaran ganda (composite unique mahasiswa + course)
            $table->unique(['course_id', 'mahasiswa_id']);
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
