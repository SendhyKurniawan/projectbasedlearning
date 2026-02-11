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
        Schema::table('material_views', function (Blueprint $table) {
            if (Schema::hasColumn('material_views', 'student_id') && !Schema::hasColumn('material_views', 'mahasiswa_id')) {
                $table->renameColumn('student_id', 'mahasiswa_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_views', function (Blueprint $table) {
             if (Schema::hasColumn('material_views', 'mahasiswa_id')) {
                $table->renameColumn('mahasiswa_id', 'student_id');
            }
        });
    }
};
