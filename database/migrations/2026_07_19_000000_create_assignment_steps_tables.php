<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fitur progresi tugas multi-step: tabel step per tugas, pengumpulan per step,
// dan mode penilaian step (final = satu nilai akhir, per_step = akumulasi nilai step).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->unsignedInteger('step_number');                       // urutan step (1, 2, 3, ...)
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('deadline')->nullable();                     // deadline opsional per step
            $table->enum('submission_format', ['pdf', 'url'])->nullable(); // null = ikut format tugas induk
            $table->unsignedInteger('max_score')->nullable();             // bobot nilai step (wajib saat mode per_step)
            $table->timestamps();

            $table->unique(['assignment_id', 'step_number'], 'assignment_steps_assignment_step_unique');
        });

        Schema::create('step_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_step_id')->constrained('assignment_steps')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->string('file_path')->nullable();
            $table->string('url_link')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['submitted', 'late', 'graded'])->default('submitted');
            $table->timestamps();

            // Satu pengumpulan per mahasiswa per step (pola sama dengan submissions per tugas).
            $table->unique(['assignment_step_id', 'mahasiswa_id'], 'step_submissions_step_mahasiswa_unique');
        });

        Schema::table('assignments', function (Blueprint $table) {
            // Mode penilaian tugas ber-step: 'final' = nilai tunggal via submission lama,
            // 'per_step' = tiap step dinilai lalu diakumulasi.
            $table->enum('step_grading_mode', ['final', 'per_step'])->default('final');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('step_grading_mode');
        });

        Schema::dropIfExists('step_submissions');
        Schema::dropIfExists('assignment_steps');
    }
};
