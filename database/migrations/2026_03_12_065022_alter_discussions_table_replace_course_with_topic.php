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
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            $table->string('topic')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('topic');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
