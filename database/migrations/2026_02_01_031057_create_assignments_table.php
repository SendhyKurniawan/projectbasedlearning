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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->integer('assignment_number')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['tugas', 'quiz', 'exercise'])->default('tugas');
            $table->enum('submission_format', ['pdf', 'url'])->default('pdf');
            $table->json('exercise_config')->nullable();
            $table->dateTime('deadline');
            $table->integer('max_score')->default(100);
            $table->foreignId('required_material_id')->nullable()->constrained('materials')->onDelete('set null');
            $table->integer('duration_minutes')->nullable();
            $table->integer('quiz_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
