<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Longgarkan keunikan kode_matkul agar mendukung multi-kelas: ganti unique global
// menjadi unique komposit (kode + semester + kelas). Ini yang memungkinkan "siblings".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Hapus unique global agar kode_matkul yang sama boleh ada di kelas berbeda.
            $table->dropUnique(['kode_matkul']);

            // Komposit: satu matkul per kombinasi (kode + semester + kelas).
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
