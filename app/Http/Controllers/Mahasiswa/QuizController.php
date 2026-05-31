<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(Assignment $assignment)
    {
        $mahasiswa = auth()->user();
        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $existingSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', auth()->id())
            ->first();

        if ($existingSubmission && $existingSubmission->finished_at) {
            return redirect()->route('mahasiswa.quizzes.result', $assignment);
        }

        return view('mahasiswa.quizzes.show', [
            'assignment' => $assignment,
            'existingSubmission' => $existingSubmission
        ]);
    }

    public function result(Assignment $assignment)
    {
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', auth()->id())
            ->whereNotNull('finished_at')
            ->firstOrFail();

        $assignment->load('questions.options');

        return view('mahasiswa.quizzes.result', [
            'assignment' => $assignment,
            'submission' => $submission
        ]);
    }

    public function start(Assignment $assignment)
    {
        $mahasiswa = auth()->user();
        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $submission = Submission::firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'mahasiswa_id' => auth()->id(),
            ],
            [
                'started_at' => now(),
            ]
        );

        return redirect()->route('mahasiswa.quizzes.take', $assignment);
    }

    public function take(Assignment $assignment)
    {
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($submission->finished_at) {
            return redirect()->route('mahasiswa.quizzes.show', $assignment)
                ->with('info', 'Anda sudah menyelesaikan kuis ini.');
        }

        $questions = $assignment->questions()->with('options')->get();

        return view('mahasiswa.quizzes.take', [
            'assignment' => $assignment,
            'submission' => $submission,
            'questions' => $questions
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($submission->finished_at) {
             return redirect()->route('mahasiswa.quizzes.show', $assignment);
        }

        $activeQuestions = $assignment->questions;

        $hasNonAutogradable = $activeQuestions
            ->whereIn('question_type', ['essay', 'code_snippet'])
            ->isNotEmpty();

        DB::transaction(function () use ($request, $submission, $activeQuestions, $hasNonAutogradable) {
            $answers = $request->input('answers', []);
            $calculatedScore = 0;

            $mcQuestionIds = $activeQuestions->where('question_type', 'pilihan_ganda')->pluck('id')->toArray();
            $submittedOptionIds = [];
            foreach ($mcQuestionIds as $qId) {
                if (isset($answers[$qId]) && is_numeric($answers[$qId])) {
                    $submittedOptionIds[] = $answers[$qId];
                }
            }

            $correctOptions = collect();
            if (!empty($submittedOptionIds)) {
                $correctOptions = QuizOption::whereIn('id', $submittedOptionIds)
                    ->where('is_correct', true)
                    ->get()
                    ->keyBy('id');
            }

            foreach ($activeQuestions as $question) {
                $userAnswer = $answers[$question->id] ?? null;

                if ($question->question_type === 'pilihan_ganda') {
                    if ($userAnswer && isset($correctOptions[$userAnswer])) {
                        $calculatedScore += $question->score_weight;
                    }
                }
            }

            // Score covers MC only; treat as final only when no essay/code_snippet questions remain.
            $submission->update([
                'finished_at' => now(),
                'score' => $calculatedScore,
                'answers' => $answers,
                'status' => $hasNonAutogradable ? 'submitted' : 'graded',
            ]);
        });

        $flash = $hasNonAutogradable
            ? 'Kuis berhasil dikumpulkan! Skor sementara dari pilihan ganda; soal essay/kode menunggu penilaian dosen.'
            : 'Kuis berhasil dikumpulkan! Skor final telah tersimpan.';

        return redirect()->route('mahasiswa.quizzes.result', $assignment)
            ->with('success', $flash);
    }
}
