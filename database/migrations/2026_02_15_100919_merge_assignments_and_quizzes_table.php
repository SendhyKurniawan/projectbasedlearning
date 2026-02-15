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
        // 1. Update Assignments Table
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('max_score');
            }
            if (!Schema::hasColumn('assignments', 'quiz_number')) {
                $table->integer('quiz_number')->nullable()->after('duration_minutes');
            }
        });

        // 2. Update Submissions Table
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'answers')) {
                $table->json('answers')->nullable()->after('code_answer');
            }
            if (!Schema::hasColumn('submissions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('submissions', 'finished_at')) {
                $table->timestamp('finished_at')->nullable()->after('started_at');
            }
        });

        // 3. Update Quiz Questions Table (Add assignment_id)
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_questions', 'assignment_id')) {
                $table->foreignId('assignment_id')->nullable()->after('quiz_id')->constrained('assignments')->onDelete('cascade');
            }
        });

        // 4. Data Migration: Quizzes -> Assignments
        if (Schema::hasTable('quizzes')) {
            $quizzes = DB::table('quizzes')->get();
            foreach ($quizzes as $quiz) {
                // Check if already migrated or exists
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

                // Update Questions to link to Assignment
                DB::table('quiz_questions')
                    ->where('quiz_id', $quiz->id)
                    ->update(['assignment_id' => $assignmentId]);

                // Data Migration: Quiz Attempts -> Submissions
                if (Schema::hasTable('quiz_attempts')) {
                    $attempts = DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->get();
                    foreach ($attempts as $attempt) {
                        DB::table('submissions')->insert([
                            'assignment_id' => $assignmentId,
                            'mahasiswa_id' => $attempt->mahasiswa_id,
                            'answers' => $attempt->answers,
                            'started_at' => $attempt->started_at,
                            'finished_at' => $attempt->finished_at,
                            'submitted_at' => $attempt->finished_at ?? $attempt->updated_at,
                            'score' => $attempt->total_score,
                            'status' => $attempt->finished_at ? 'submitted' : 'late', // approximate
                            'created_at' => $attempt->created_at,
                            'updated_at' => $attempt->updated_at,
                        ]);
                    }
                }
            }
        }

        // 5. Cleanup redundant tables and columns
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'quiz_id')) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            }
            // Make assignment_id not nullable after migration
            $table->foreignId('assignment_id')->nullable(false)->change();
        });

        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quizzes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse logic would be complex due to data movements, 
        // usually for this kind of consolidation we just drop what we added.
        Schema::table('quiz_questions', function (Blueprint $table) {
             $table->unsignedBigInteger('quiz_id')->nullable()->after('assignment_id');
             // Cannot easily restore data and tables here without more complex logic
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['answers', 'started_at', 'finished_at']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['duration_minutes', 'quiz_number']);
        });
    }
};
