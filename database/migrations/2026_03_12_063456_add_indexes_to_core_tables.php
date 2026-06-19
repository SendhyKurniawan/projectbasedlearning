<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahkan index pada kolom-kolom yang sering difilter/di-join di tabel inti,
// untuk mempercepat query (filter peran, relasi foreign key, dll).
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Index untuk tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
            $table->index('student_class_id');
        });

        // Index untuk tabel courses
        Schema::table('courses', function (Blueprint $table) {
            $table->index('semester_id');
            $table->index('student_class_id');
            $table->index('dosen_id');
        });

        // Index untuk tabel enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('mahasiswa_id');
        });

        // Index untuk tabel materials
        Schema::table('materials', function (Blueprint $table) {
            $table->index('course_id');
        });

        // Index untuk tabel assignments
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('course_id');
        });

        // Index untuk tabel submissions
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('assignment_id');
            $table->index('mahasiswa_id');
        });

        // Index untuk tabel student_classes
        Schema::table('student_classes', function (Blueprint $table) {
            $table->index('study_program_id');
            $table->index('semester_id');
        });

        // Index untuk tabel semesters
        Schema::table('semesters', function (Blueprint $table) {
            $table->index('academic_year_id');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['student_class_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['semester_id']);
            $table->dropIndex(['student_class_id']);
            $table->dropIndex(['dosen_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['mahasiswa_id']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['assignment_id']);
            $table->dropIndex(['mahasiswa_id']);
        });

        Schema::table('student_classes', function (Blueprint $table) {
            $table->dropIndex(['study_program_id']);
            $table->dropIndex(['semester_id']);
        });

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
        });
    }
};
