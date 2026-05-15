<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop global unique so same kode_matkul can exist across different kelas
            $table->dropUnique(['kode_matkul']);

            // Composite: one course per (code + semester + kelas) per dosen
            $table->unique(['kode_matkul', 'semester_id', 'student_class_id'], 'courses_code_semester_class_unique');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_code_semester_class_unique');
            $table->unique('kode_matkul');
        });
    }
};
