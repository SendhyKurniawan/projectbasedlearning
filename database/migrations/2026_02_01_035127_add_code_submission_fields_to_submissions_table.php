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
            $table->text('code_answer')->nullable()->after('notes');
            $table->json('validation_result')->nullable()->after('code_answer');
            $table->boolean('auto_graded')->default(false)->after('validation_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['code_answer', 'validation_result', 'auto_graded']);
        });
    }
};
