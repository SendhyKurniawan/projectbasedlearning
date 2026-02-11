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
            $table->enum('type', ['quiz', 'exercise', 'project'])->default('project')->after('max_score');
            $table->json('exercise_config')->nullable()->after('type');
            $table->boolean('auto_grade')->default(false)->after('exercise_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['type', 'exercise_config', 'auto_grade']);
        });
    }
};
