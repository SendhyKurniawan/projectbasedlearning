<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        // Check availability (e.g. course enrollment) - assumes middleware handles course access
        // Check if already taken?
        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('mahasiswa_id', auth()->id())
            ->first();

        if ($existingAttempt && $existingAttempt->finished_at) {
            return redirect()->route('mahasiswa.quizzes.result', $quiz);
        }

        return view('mahasiswa.quizzes.show', compact('quiz', 'existingAttempt'));
    }

    public function result(Quiz $quiz)
    {
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('mahasiswa_id', auth()->id())
            ->whereNotNull('finished_at')
            ->firstOrFail();

        $quiz->load('questions.options');

        return view('mahasiswa.quizzes.result', compact('quiz', 'attempt'));
    }

    public function start(Quiz $quiz)
    {
        // Check strict deadlines if needed
        
        $attempt = QuizAttempt::firstOrCreate(
            [
                'quiz_id' => $quiz->id,
                'mahasiswa_id' => auth()->id(),
            ],
            [
                'started_at' => now(),
            ]
        );

        return redirect()->route('mahasiswa.quizzes.take', $quiz);
    }

    public function take(Quiz $quiz)
    {
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($attempt->finished_at) {
            return redirect()->route('mahasiswa.quizzes.show', $quiz)
                ->with('info', 'You have already finished this quiz.');
        }

        // Load questions without answers (for security, though in blade we just don't show them)
        // Ideally we select specific columns, but for now lazy load is fine
        $questions = $quiz->questions()->with('options')->get();

        return view('mahasiswa.quizzes.take', compact('quiz', 'attempt', 'questions'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($attempt->finished_at) {
             return redirect()->route('mahasiswa.quizzes.show', $quiz);
        }

        // Calculate Score
        $totalScore = 0;
        $activeQuestions = $quiz->questions;

        DB::transaction(function () use ($request, $quiz, $attempt, &$totalScore, $activeQuestions) {
            // Save answers logic - for now we calculate score directly for MC
            // For Essay/Code, we might need a separate 'answers' table if we want to store user input persistently
            // For MVP, lets assume we mark MC auto and Essay manual (but where to store essay?)
            
            // WAIT: The ERD v2 didn't strictly specify a 'quiz_answers' table for user input storage.
            // But 'QuizAttempts' has 'total_score'.
            // To properly grade Essay/Code, we MUST store the student's answer.
            // I will assume for now we might be missing a 'quiz_answers' table in the migration I made based on user prompt?
            // The prompt "ERD v2" listed "QuizAttempts" but didn't explicitly detail "QuizAnswers" table in the text, 
            // but functionally it's required. 
            // I'll check my migration again. I did NOT create a quiz_answers table.
            // This is a gap. I should probably create it or store it in a JSON column in attempts?
            // "QuizAttempts" is usually for the session.
            
            // Let's look at the ERD text again:
            // "9️⃣ Quiz Attempts ... total_score"
            // It doesn't list a separate answers table. 
            // However, without it, we can't store the code/essay the student wrote.
            // I will add a JSON 'answers' column to QuizAttempts to store the raw answers given by student.
            
            $answers = $request->input('answers', []);
            $calculatedScore = 0;

            foreach ($activeQuestions as $question) {
                $userAnswer = $answers[$question->id] ?? null;

                if ($question->question_type === 'pilihan_ganda') {
                     // Find selected option
                     if ($userAnswer) {
                         $selectedOption = QuizOption::find($userAnswer);
                         if ($selectedOption && $selectedOption->question_id == $question->id && $selectedOption->is_correct) {
                             $calculatedScore += $question->score_weight;
                         }
                     }
                } else {
                    // Manual grade needed for Essay/Code
                    // For now, we don't add score automatically
                }
            }

            $attempt->update([
                'finished_at' => now(),
                'total_score' => $calculatedScore, // Provisional score (MC only)
                'answers' => $answers,
            ]);
        });

        return redirect()->route('mahasiswa.quizzes.result', $quiz)
            ->with('success', 'Quiz submitted! Your score (multiple choice only) is saved.');
    }
}
