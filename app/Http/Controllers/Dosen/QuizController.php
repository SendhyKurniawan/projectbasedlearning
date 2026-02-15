<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    // --- Quiz Management ---

    public function index(Course $course)
    {
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $quizzes = $course->quizzes()->latest()->get();
        return view('dosen.quizzes.index', compact('course', 'quizzes'));
    }

    public function create(Course $course)
    {
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        return view('dosen.quizzes.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'quiz_number' => 'required|integer',
            'type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $course->quizzes()->create($request->all());

        return redirect()->route('dosen.quizzes.index', $course)
            ->with('success', 'Quiz created successfully.');
    }

    public function edit(Quiz $quiz)
    {
        if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        return view('dosen.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'quiz_number' => 'required|integer',
            'type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $quiz->update($request->all());

        return redirect()->route('dosen.quizzes.index', $quiz->course)
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $quiz->delete();
        return back()->with('success', 'Quiz deleted successfully.');
    }

    // --- Question Management ---

    public function questions(Quiz $quiz)
    {
         if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $questions = $quiz->questions;
        return view('dosen.quizzes.questions.index', compact('quiz', 'questions'));
    }

    public function createQuestion(Quiz $quiz)
    {
         if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        return view('dosen.quizzes.questions.create', compact('quiz'));
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
         if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'score_weight' => 'required|integer|min:1',
            'correct_answer' => 'nullable|string', // For essay/code
            'options' => 'nullable|array', // For multiple choice
            'options.*.text' => 'required_with:options|string',
        ]);

        DB::transaction(function () use ($request, $quiz) {
            $question = $quiz->questions()->create([
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'score_weight' => $request->score_weight,
                'correct_answer' => $request->correct_answer,
            ]);

            if ($request->question_type === 'pilihan_ganda' && $request->has('options')) {
                foreach ($request->options as $opt) {
                    $question->options()->create([
                        'option_text' => $opt['text'],
                        'is_correct' => isset($opt['is_correct']) && $opt['is_correct'] == 1,
                    ]);
                }
            }
        });

        return redirect()->route('dosen.quizzes.questions.index', $quiz)
            ->with('success', 'Question added successfully.');
    }

    public function editQuestion(QuizQuestion $question)
    {
         if ($question->quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        return view('dosen.quizzes.questions.edit', compact('question'));
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
         if ($question->quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'score_weight' => 'required|integer|min:1',
            'correct_answer' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.text' => 'required_with:options|string',
        ]);

        DB::transaction(function () use ($request, $question) {
            $question->update([
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'score_weight' => $request->score_weight,
                'correct_answer' => $request->correct_answer,
            ]);

            if ($request->question_type === 'pilihan_ganda' && $request->has('options')) {
                $question->options()->delete(); // Easier to recreate for now
                 foreach ($request->options as $opt) {
                    $question->options()->create([
                        'option_text' => $opt['text'],
                        'is_correct' => isset($opt['is_correct']) && $opt['is_correct'] == 1,
                    ]);
                }
            }
        });

        return redirect()->route('dosen.quizzes.questions.index', $question->quiz)
            ->with('success', 'Question updated successfully.');
    }

    public function destroyQuestion(QuizQuestion $question)
    {
         if ($question->quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    // --- Attempts Management ---

    public function attempts(Quiz $quiz)
    {
        if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $attempts = $quiz->attempts()->with('mahasiswa')->latest()->get();
        
        return view('dosen.quizzes.attempts.index', compact('quiz', 'attempts'));
    }

    public function showAttempt(Quiz $quiz, QuizAttempt $attempt)
    {
        if ($quiz->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        // Ensure attempt belongs to quiz
        if($attempt->quiz_id !== $quiz->id){
            abort(404);
        }

        $quiz->load('questions.options');

        return view('dosen.quizzes.attempts.show', compact('quiz', 'attempt'));
    }
}
