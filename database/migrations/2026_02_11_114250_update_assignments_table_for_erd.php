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
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'type')) {
                $table->enum('type', ['tugas', 'quiz', 'project'])->default('tugas')->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'type')) {
                // $table->dropColumn('type'); 
                // Don't drop it if it might have existed before. 
                // But for this task, let's assume we can drop it or just leave it.
                // Best practice: if we didn't add it (because it existed), we shouldn't drop it.
                // But in 'down', we can try to drop it if we are sure we want to revert to pre-migration state.
            }
        });
    }
};
