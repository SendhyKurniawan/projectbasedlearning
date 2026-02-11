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
        Schema::table('submissions', function (Blueprint $table) {
             if (Schema::hasColumn('submissions', 'student_id') && !Schema::hasColumn('submissions', 'mahasiswa_id')) {
                 $table->renameColumn('student_id', 'mahasiswa_id');
             }
             
             if (!Schema::hasColumn('submissions', 'url_link')) {
                 $table->string('url_link')->nullable()->after('file_path');
             }
             
             if (!Schema::hasColumn('submissions', 'status')) {
                 $table->string('status')->default('submitted')->after('url_link'); // Fix: after 'url_link' which we just added or confirmed exists
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['url_link', 'status']);
            if (Schema::hasColumn('submissions', 'mahasiswa_id')) {
                $table->renameColumn('mahasiswa_id', 'student_id');
            }
        });
    }
};
