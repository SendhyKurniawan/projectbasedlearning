<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $assignments = $course->assignments()
            ->withCount('submissions')
            ->orderBy('deadline', 'desc')
            ->get();
        
        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|boolean',
            'submission_format' => 'nullable|in:pdf,url',
            'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
        ]);
        
        $count = Assignment::where('course_id', $course->id)
            ->where('type', $request->type)
            ->count();
        $nextNumber = $count + 1;

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'type' => $request->type,
            'submission_format' => $request->type === 'tugas' ? $request->submission_format : 'pdf',
            'quiz_number' => $request->type === 'quiz' ? $nextNumber : null,
            'assignment_number' => $request->type !== 'quiz' ? $nextNumber : null,
            'duration_minutes' => $request->has_duration ? $request->duration_minutes : null,
        ]);
        
        if ($request->type === 'quiz') {
            return redirect()->route('dosen.assignments.questions.index', $assignment)
                ->with('success', 'Quiz berhasil dibuat! Silakan tambahkan pertanyaan.');
        }
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil ditambahkan!');
    }

    public function edit(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.assignments.edit', compact('assignment', 'course'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|boolean',
            'submission_format' => 'nullable|in:pdf,url',
            'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
        ]);
        
        $data = $request->only([
            'title', 'description', 'deadline', 'max_score', 'type'
        ]);
        $data['duration_minutes'] = $request->has_duration ? $request->duration_minutes : null;
        $data['submission_format'] = $request->type === 'tugas' ? $request->submission_format : 'pdf';

        $assignment->update($data);
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil diperbarui!');
    }

    public function destroy(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $assignment->delete();
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil dihapus!');
    }

    public function submissions(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $submissions = $assignment->submissions()
            ->with('mahasiswa')
            ->orderBy($assignment->type === 'quiz' ? 'finished_at' : 'submitted_at', 'desc')
            ->get();
        
        if ($assignment->type === 'quiz') {
            return view('dosen.assignments.quiz_attempts', compact('assignment', 'course', 'submissions'));
        }
        
        return view('dosen.assignments.submissions', compact('assignment', 'course', 'submissions'));
    }

    public function showQuizAttempt(Assignment $assignment, Submission $submission)
    {
        // Eager-load course to avoid lazy-loading in the authorization check
        $assignment->loadMissing('course');
        if ($assignment->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $assignment->load('questions.options');

        return view('dosen.assignments.quiz_attempt_show', compact('assignment', 'submission'));
    }

    public function grade(Request $request, Submission $submission)
    {
        // Eager-load to avoid chained lazy-loading ($submission->assignment->course)
        $submission->loadMissing(['assignment.course']);
        $assignment = $submission->assignment;
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'score' => 'required|integer|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string',
        ]);
        
        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'graded'
        ]);
        
        return redirect()->back()
            ->with('success', 'Nilai berhasil diberikan!');
    }

    // --- Question Management (Absorbed from QuizController) ---

    public function questions(Assignment $assignment)
    {
        if ($assignment->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $questions = $assignment->questions;
        return view('dosen.assignments.questions.index', compact('assignment', 'questions'));
    }

    public function createQuestion(Assignment $assignment)
    {
        if ($assignment->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        return view('dosen.assignments.questions.create', compact('assignment'));
    }

    public function storeQuestion(Request $request, Assignment $assignment)
    {
        if ($assignment->course->dosen_id !== auth()->id()) {
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $assignment) {
            $question = $assignment->questions()->create([
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

        return redirect()->route('dosen.assignments.questions.index', $assignment)
            ->with('success', 'Pertanyaan berhasil ditambahkan!');
    }

    public function editQuestion(\App\Models\QuizQuestion $question)
    {
        if ($question->assignment->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $assignment = $question->assignment;
        return view('dosen.assignments.questions.edit', compact('question', 'assignment'));
    }

    public function updateQuestion(Request $request, \App\Models\QuizQuestion $question)
    {
        if ($question->assignment->course->dosen_id !== auth()->id()) {
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $question) {
            $question->update([
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'score_weight' => $request->score_weight,
                'correct_answer' => $request->correct_answer,
            ]);

            if ($request->question_type === 'pilihan_ganda' && $request->has('options')) {
                $question->options()->delete();
                foreach ($request->options as $opt) {
                    $question->options()->create([
                        'option_text' => $opt['text'],
                        'is_correct' => isset($opt['is_correct']) && $opt['is_correct'] == 1,
                    ]);
                }
            }
        });

        return redirect()->route('dosen.assignments.questions.index', $question->assignment)
            ->with('success', 'Pertanyaan berhasil diperbarui!');
    }

    public function destroyQuestion(\App\Models\QuizQuestion $question)
    {
        if ($question->assignment->course->dosen_id !== auth()->id()) {
            abort(403);
        }
        $assignment = $question->assignment;
        $question->delete();
        return redirect()->route('dosen.assignments.questions.index', $assignment)
            ->with('success', 'Pertanyaan berhasil dihapus!');
    }
}
