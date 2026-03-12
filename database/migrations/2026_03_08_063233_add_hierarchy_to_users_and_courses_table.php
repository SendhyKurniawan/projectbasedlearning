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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('student_class_id')->nullable()->constrained('student_classes')->nullOnDelete();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('student_class_id')->nullable()->constrained('student_classes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['student_class_id']);
            $table->dropColumn('student_class_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['student_class_id']);
            $table->dropColumn('student_class_id');
        });
    }
};
