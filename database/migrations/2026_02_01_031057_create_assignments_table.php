<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel assignments (tugas). Satu tabel menampung tiga jenis lewat kolom 'type':
// tugas, quiz, dan exercise. Detail exercise disimpan di kolom JSON exercise_config.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->integer('assignment_number')->nullable(); // nomor urut tugas (non-quiz)
            $table->text('description')->nullable();
            $table->enum('type', ['tugas', 'quiz', 'exercise'])->default('tugas'); // jenis penilaian
            $table->enum('submission_format', ['pdf', 'url'])->default('pdf'); // format pengumpulan tugas
            $table->json('exercise_config')->nullable(); // konfigurasi exercise: bahasa, starter/solution code, keyword, hint
            $table->dateTime('deadline');
            $table->integer('max_score')->default(100);
            $table->foreignId('required_material_id')->nullable()->constrained('materials')->onDelete('set null'); // materi prasyarat agar tugas terbuka
            $table->integer('duration_minutes')->nullable();
            $table->integer('quiz_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
