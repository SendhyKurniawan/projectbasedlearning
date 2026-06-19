<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migrasi gabungan fitur quiz & kelompok: buat tabel groups, group_members, quiz_questions,
// quiz_options, lalu tambahkan kolom group_id ke tabel submissions.
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel kelompok (groups)
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->string('group_name');
            $table->timestamps();
        });

        // 2. Tabel anggota kelompok (group_members)
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Tabel soal quiz (quiz_questions)
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['essay', 'pilihan_ganda', 'code_snippet']);
            $table->text('correct_answer')->nullable();
            $table->integer('score_weight')->default(1);
            $table->timestamps();
        });

        // 4. Tabel opsi jawaban (quiz_options) untuk soal pilihan ganda
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 5. Tambahkan kolom group_id ke submissions (untuk pengumpulan kelompok)
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null')->after('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
