<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to Users
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
            $table->index('student_class_id');
        });

        // Add indexes to Courses
        Schema::table('courses', function (Blueprint $table) {
            $table->index('semester_id');
            $table->index('student_class_id');
            $table->index('dosen_id');
        });

        // Add indexes to Enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('mahasiswa_id');
        });

        // Add indexes to Materials
        Schema::table('materials', function (Blueprint $table) {
            $table->index('course_id');
        });

        // Add indexes to Assignments
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('course_id');
        });

        // Add indexes to Submissions
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('assignment_id');
            $table->index('mahasiswa_id');
        });

        // Add indexes to Student Classes
        Schema::table('student_classes', function (Blueprint $table) {
            $table->index('study_program_id');
            $table->index('semester_id');
        });

        // Add indexes to Semesters
        Schema::table('semesters', function (Blueprint $table) {
            $table->index('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
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
