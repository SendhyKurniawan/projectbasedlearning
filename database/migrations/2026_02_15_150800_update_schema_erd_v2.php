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
        // 1. Update Assignments
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'assignment_number')) {
                $table->integer('assignment_number')->nullable()->after('title');
            }
            if (!Schema::hasColumn('assignments', 'submission_format')) {
                $table->enum('submission_format', ['pdf', 'url'])->default('pdf')->after('type');
            }
        });

        // 2. Groups Table
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
                $table->string('group_name');
                $table->timestamps();
            });
        }

        // 3. Group Members Table
        if (!Schema::hasTable('group_members')) {
            Schema::create('group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
                $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // 4. Update Submissions
        Schema::table('submissions', function (Blueprint $table) {
             if (!Schema::hasColumn('submissions', 'group_id')) {
                 $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null')->after('assignment_id');
             }
        });

        // 5. Quizzes Table
        if (!Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
                $table->integer('quiz_number');
                $table->enum('type', ['essay', 'pilihan_ganda', 'code_snippet']);
                $table->string('title');
                $table->integer('duration_minutes')->default(60);
                $table->timestamps();
            });
        }

        // 6. Quiz Questions Table
        if (!Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
                $table->text('question_text');
                $table->enum('question_type', ['essay', 'pilihan_ganda', 'code_snippet']);
                $table->text('correct_answer')->nullable(); // For essay/code_snippet
                $table->integer('score_weight')->default(1);
                $table->timestamps();
            });
        }

        // 7. Quiz Options Table
        if (!Schema::hasTable('quiz_options')) {
            Schema::create('quiz_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
                $table->string('option_text');
                $table->boolean('is_correct')->default(false);
                $table->timestamps();
            });
        }

        // 8. Quiz Attempts Table
        if (!Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
                $table->foreignId('mahasiswa_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('started_at');
                $table->timestamp('finished_at')->nullable();
                $table->integer('total_score')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['assignment_number', 'submission_format']);
        });
    }
};
