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
        if (Schema::hasTable('course_student') && !Schema::hasTable('enrollments')) {
            Schema::rename('course_student', 'enrollments');
        }

        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'student_id') && !Schema::hasColumn('enrollments', 'mahasiswa_id')) {
                $table->renameColumn('student_id', 'mahasiswa_id');
            }
            
            if (!Schema::hasColumn('enrollments', 'final_grade')) {
                $table->string('final_grade')->nullable()->after('enrolled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
             if (Schema::hasColumn('enrollments', 'final_grade')) {
                $table->dropColumn('final_grade');
             }
             if (Schema::hasColumn('enrollments', 'mahasiswa_id')) {
                $table->renameColumn('mahasiswa_id', 'student_id');
             }
        });
        
        if (Schema::hasTable('enrollments') && !Schema::hasTable('course_student')) {
            Schema::rename('enrollments', 'course_student');
        }
    }
};
