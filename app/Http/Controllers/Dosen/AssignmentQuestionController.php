<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        return view('dosen.quizzes.questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'score_weight' => 'required|integer|min:1',
            'correct_answer' => 'nullable|required_if:question_type,essay,code_snippet', // Required for essay/code
            'options' => 'nullable|required_if:question_type,pilihan_ganda|array|min:2',
            'options.*.text' => 'required_with:options|string',
            'options.*.is_correct' => 'boolean',
        ]);

        DB::transaction(function () use ($quiz, $validated) {
            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'score_weight' => $validated['score_weight'],
                'correct_answer' => $validated['correct_answer'] ?? null,
            ]);

            if ($validated['question_type'] === 'pilihan_ganda' && isset($validated['options'])) {
                foreach ($validated['options'] as $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['text'],
                        'is_correct' => isset($optionData['is_correct']) && $optionData['is_correct'] == '1',
                    ]);
                }
            }
        });

        return redirect()->route('dosen.quizzes.edit', $quiz)
            ->with('success', 'Question added successfully.');
    }

    public function edit(Quiz $quiz, QuizQuestion $question)
    {
        return view('dosen.quizzes.questions.edit', compact('quiz', 'question'));
    }

    public function update(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'score_weight' => 'required|integer|min:1',
            'correct_answer' => 'nullable|required_if:question_type,essay,code_snippet',
            'options' => 'nullable|required_if:question_type,pilihan_ganda|array|min:2',
            'options.*.id' => 'nullable|integer', // Check if existing option
            'options.*.text' => 'required_with:options|string',
            'options.*.is_correct' => 'boolean',
        ]);

        DB::transaction(function () use ($question, $validated) {
            $question->update([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'score_weight' => $validated['score_weight'],
                'correct_answer' => $validated['correct_answer'] ?? null,
            ]);

            if ($validated['question_type'] === 'pilihan_ganda' && isset($validated['options'])) {
                // Simplified: Delete all options and recreate (easiest for now, or sync)
                // Better: Update existing, create new, delete missing.
                // For simplicity MVP: Delete all and recreate.
                $question->options()->delete();
                
                foreach ($validated['options'] as $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['text'],
                        'is_correct' => isset($optionData['is_correct']) && $optionData['is_correct'] == '1',
                    ]);
                }
            }
        });

        return redirect()->route('dosen.quizzes.edit', $quiz)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return redirect()->route('dosen.quizzes.edit', $quiz)
            ->with('success', 'Question deleted successfully.');
    }
}
