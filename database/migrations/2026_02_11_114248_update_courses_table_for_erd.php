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
        Schema::table('courses', function (Blueprint $table) {
            // Rename columns to match ERD if they exist in old format
            if (Schema::hasColumn('courses', 'code') && !Schema::hasColumn('courses', 'kode_matkul')) {
                $table->renameColumn('code', 'kode_matkul');
            }
            
            if (Schema::hasColumn('courses', 'name') && !Schema::hasColumn('courses', 'nama_matkul')) {
                $table->renameColumn('name', 'nama_matkul');
            }

            // Add new columns
            if (!Schema::hasColumn('courses', 'sks')) {
                // Use 'kode_matkul' if it exists (renamed or original), otherwise fallback or default
                $after = Schema::hasColumn('courses', 'kode_matkul') ? 'kode_matkul' : null;
                $col = $table->integer('sks')->default(3);
                if ($after) {
                    $col->after($after);
                }
            }
            
            if (!Schema::hasColumn('courses', 'semester_id')) {
                 $table->foreignId('semester_id')->nullable()->constrained()->onDelete('set null')->after('dosen_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['semester_id', 'sks']);
            // We didn't rename, so no need to reverse rename
        });
    }
};
