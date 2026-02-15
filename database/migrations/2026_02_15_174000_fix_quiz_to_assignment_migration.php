<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Finalize Data Migration if tables still exist
        if (Schema::hasTable('quizzes')) {
            $quizzes = DB::table('quizzes')->get();
            foreach ($quizzes as $quiz) {
                // Check if this quiz already has a corresponding assignment
                // We match by title and course_id as a heuristic
                $existingAssignment = DB::table('assignments')
                    ->where('course_id', $quiz->course_id)
                    ->where('title', $quiz->title)
                    ->where('type', 'quiz')
                    ->first();

                if (!$existingAssignment) {
                    $assignmentId = DB::table('assignments')->insertGetId([
                        'course_id' => $quiz->course_id,
                        'title' => $quiz->title,
                        'type' => 'quiz',
                        'quiz_number' => $quiz->quiz_number,
                        'duration_minutes' => $quiz->duration_minutes,
                        'deadline' => now()->addDays(7), // Default deadline for migrated quizzes
                        'created_at' => $quiz->created_at,
                        'updated_at' => $quiz->updated_at,
                    ]);
                } else {
                    $assignmentId = $existingAssignment->id;
                }

                // Update Questions to link to Assignment if they are still linked to the old quiz
                DB::table('quiz_questions')
                    ->where('quiz_id', $quiz->id)
                    ->update(['assignment_id' => $assignmentId]);

                // Migrate Attempts to Submissions if needed
                if (Schema::hasTable('quiz_attempts')) {
                    $attempts = DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->get();
                    foreach ($attempts as $attempt) {
                        // Check if submission already exists
                        $exists = DB::table('submissions')
                            ->where('assignment_id', $assignmentId)
                            ->where('mahasiswa_id', $attempt->mahasiswa_id)
                            ->where('started_at', $attempt->started_at)
                            ->exists();

                        if (!$exists) {
                            DB::table('submissions')->insert([
                                'assignment_id' => $assignmentId,
                                'mahasiswa_id' => $attempt->mahasiswa_id,
                                'answers' => $attempt->answers,
                                'started_at' => $attempt->started_at,
                                'finished_at' => $attempt->finished_at,
                                'submitted_at' => $attempt->finished_at ?? $attempt->updated_at,
                                'score' => $attempt->total_score,
                                'status' => $attempt->finished_at ? 'submitted' : 'late',
                                'created_at' => $attempt->created_at,
                                'updated_at' => $attempt->updated_at,
                            ]);
                        }
                    }
                }
            }
        }

        // 2. Clean up quiz_questions table
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'quiz_id')) {
                // Try to drop foreign key first if it exists
                try {
                    $table->dropForeign(['quiz_id']);
                } catch (\Exception $e) {
                    // Ignore if no foreign key
                }
                $table->dropColumn('quiz_id');
            }
            
            // Make assignment_id NOT NULL and ensure it's a fixed foreign key
            if (Schema::hasColumn('quiz_questions', 'assignment_id')) {
                $table->unsignedBigInteger('assignment_id')->nullable(false)->change();
            }
        });

        // 3. Drop redundant tables
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quizzes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing this would be destructive or very complex.
    }
};
