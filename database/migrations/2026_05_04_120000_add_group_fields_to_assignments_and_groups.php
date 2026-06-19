<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lengkapi fitur tugas kelompok: tambahkan pengaturan kelompok ke assignments,
// kolom pembuat ke groups, dan keunikan anggota per kelompok.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->boolean('is_group')->default(false);                              // apakah tugas dikerjakan berkelompok
            $table->unsignedInteger('max_group_size')->nullable();                   // batas maksimal anggota kelompok
            $table->enum('grading_mode', ['equal', 'individual'])->default('equal'); // mode penilaian: sama rata / per individu
        });

        Schema::table('groups', function (Blueprint $table) {
            // Mahasiswa pembuat/ketua kelompok.
            $table->foreignId('created_by_mahasiswa_id')->nullable()->after('group_name')
                ->constrained('users')->onDelete('set null');
        });

        Schema::table('group_members', function (Blueprint $table) {
            // Cegah seorang mahasiswa terdaftar dua kali pada kelompok yang sama.
            $table->unique(['group_id', 'mahasiswa_id'], 'group_members_group_mahasiswa_unique');
        });
    }

    public function down(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropUnique('group_members_group_mahasiswa_unique');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['created_by_mahasiswa_id']);
            $table->dropColumn('created_by_mahasiswa_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['is_group', 'max_group_size', 'grading_mode']);
        });
    }
};
