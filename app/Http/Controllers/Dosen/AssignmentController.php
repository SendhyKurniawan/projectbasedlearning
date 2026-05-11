<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Group;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        // Using Policy for authorization instead of manual check
        $this->authorize('view', $course);

        $assignments = $course->assignments()
            ->withCount('submissions')
            ->orderBy('order')
            ->orderBy('deadline', 'desc')
            ->get();

        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        // Using Policy for authorization instead of manual check
        $this->authorize('create', $course);

        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);

        return view('dosen.assignments.create', compact('course', 'materials'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('create', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|boolean',
            'submission_format' => 'nullable|in:pdf,url',
            'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
            'is_group' => 'nullable|boolean',
            'max_group_size' => 'nullable|integer|min:2|max:20',
            'grading_mode' => 'nullable|in:equal,individual',
        ]);

        $count = Assignment::where('course_id', $course->id)
            ->where('type', $request->type)
            ->count();
        $nextNumber = $count + 1;

        $maxOrder = $course->assignments()->max('order') ?? 0;

        $isGroup = $request->type === 'tugas' && $request->boolean('is_group');

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
            'order' => $maxOrder + 1,
            'is_group' => $isGroup,
            'max_group_size' => $isGroup ? $request->max_group_size : null,
            'grading_mode' => $isGroup ? ($request->grading_mode ?: 'equal') : 'equal',
        ]);
        
        if ($request->type === 'quiz') {
            return redirect()->route('dosen.assignments.questions.index', $assignment)
                ->with('success', 'Quiz berhasil dibuat! Silakan tambahkan pertanyaan.');
        }
        
        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();
        if ($students->isNotEmpty()) {
            $typeLabel = ucfirst($request->type);
            Notification::send($students, new AcademicUpdateNotification(
                "{$typeLabel} Baru Ditambahkan",
                "{$typeLabel} baru '{$assignment->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.courses.show', $course) // Could link directly if there's a show route
            ));
        }

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil ditambahkan!');
    }

    public function edit(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;

        // Using Policy for authorization instead of manual check
        $this->authorize('view', $course);

        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);

        return view('dosen.assignments.edit', compact('assignment', 'course', 'materials'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $assignment->loadMissing('course');
        $course = $assignment->course;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|boolean',
            'submission_format' => 'nullable|in:pdf,url',
            'duration_minutes' => 'nullable|integer|min:1|required_if:has_duration,true',
            'is_group' => 'nullable|boolean',
            'max_group_size' => 'nullable|integer|min:2|max:20',
            'grading_mode' => 'nullable|in:equal,individual',
        ]);

        $data = $request->only([
            'title', 'description', 'deadline', 'max_score', 'type'
        ]);
        $data['duration_minutes'] = $request->has_duration ? $request->duration_minutes : null;
        $data['submission_format'] = $request->type === 'tugas' ? $request->submission_format : 'pdf';

        $isGroup = $request->type === 'tugas' && $request->boolean('is_group');
        $hasSubmission = $assignment->submissions()->exists();

        // Lock group toggle + grading_mode after first submission to keep data consistent
        if ($hasSubmission) {
            $data['is_group'] = $assignment->is_group;
            $data['max_group_size'] = $assignment->max_group_size;
            $data['grading_mode'] = $assignment->grading_mode;
        } else {
            $data['is_group'] = $isGroup;
            $data['max_group_size'] = $isGroup ? $request->max_group_size : null;
            $data['grading_mode'] = $isGroup ? ($request->grading_mode ?: 'equal') : 'equal';
        }

        $assignment->update($data);
        
        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();
        if ($students->isNotEmpty()) {
            $typeLabel = ucfirst($assignment->type);
            Notification::send($students, new AcademicUpdateNotification(
                "{$typeLabel} Diperbarui",
                "{$typeLabel} '{$assignment->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.",
                route('mahasiswa.courses.show', $course)
            ));
        }

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil diperbarui!');
    }

    public function destroy(Assignment $assignment)
    {
        // Using Policy for authorization instead of manual check
        $this->authorize('delete', $assignment);

        $assignment->loadMissing('course');
        $course = $assignment->course;

        $assignment->delete();

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil dihapus!');
    }

    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:assignments,id',
        ]);
        
        // Ambil urutan yang ada saat ini untuk id yang diberikan dan urutkan
        $assignments = Assignment::whereIn('id', $request->ordered_ids)
                                 ->where('course_id', $course->id)
                                 ->orderBy('order')
                                 ->get();
        
        $orders = $assignments->pluck('order')->toArray();
        sort($orders);
        
        $currentOrder = 1;
        foreach ($orders as &$ord) {
            if ($ord < $currentOrder) {
                $ord = $currentOrder;
            }
            $currentOrder = $ord + 1;
        }
        unset($ord);
        
        foreach ($request->ordered_ids as $index => $id) {
            Assignment::where('id', $id)
                    ->where('course_id', $course->id)
                    ->update(['order' => $orders[$index] ?? ($index + 1)]);
        }
        
        return response()->json(['message' => 'Urutan berhasil diperbarui']);
    }

    public function submissions(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;

        // Authorization check: Use Policy instead of manual check
        $this->authorize('view', $course);

        $submissions = $assignment->submissions()
            ->with(['mahasiswa', 'group.members.mahasiswa', 'group.creator'])
            ->orderBy($assignment->type === 'quiz' ? 'finished_at' : 'submitted_at', 'desc')
            ->get();

        if ($assignment->type === 'quiz') {
            return view('dosen.assignments.quiz_attempts', compact('assignment', 'course', 'submissions'));
        }

        $groups = collect();
        if ($assignment->is_group) {
            $groups = $assignment->groups()
                ->with(['members.mahasiswa', 'creator', 'submissions.mahasiswa'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('dosen.assignments.submissions', compact('assignment', 'course', 'submissions', 'groups'));
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

        $this->authorize('view', $course);

        $request->validate([
            'score' => 'required|integer|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'graded'
        ]);

        // Notify student
        $student = $submission->mahasiswa;
        if ($student) {
            Notification::send($student, new \App\Notifications\GradeNotification(
                $assignment->title,
                $course->id
            ));
        }

        return redirect()->back()
            ->with('success', 'Nilai berhasil diberikan!');
    }

    public function gradeGroup(Request $request, Group $group)
    {
        $group->loadMissing(['assignment.course', 'submissions.mahasiswa']);
        $assignment = $group->assignment;
        $course = $assignment->course;

        $this->authorize('view', $course);

        $maxScore = $assignment->max_score;

        if ($assignment->grading_mode === 'individual') {
            $request->validate([
                'scores' => 'required|array',
                'scores.*' => 'nullable|integer|min:0|max:' . $maxScore,
                'feedbacks' => 'nullable|array',
                'feedbacks.*' => 'nullable|string',
            ]);

            foreach ($group->submissions as $submission) {
                $score = $request->input("scores.{$submission->mahasiswa_id}");
                $feedback = $request->input("feedbacks.{$submission->mahasiswa_id}");

                $submission->update([
                    'score' => $score !== null && $score !== '' ? (int) $score : null,
                    'feedback' => $feedback,
                    'status' => $score !== null && $score !== '' ? 'graded' : $submission->status,
                ]);
            }
        } else {
            $request->validate([
                'score' => 'required|integer|min:0|max:' . $maxScore,
                'feedback' => 'nullable|string',
            ]);

            Submission::where('group_id', $group->id)->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'status' => 'graded',
            ]);
        }

        // Notify all members
        $members = $group->submissions->pluck('mahasiswa')->filter()->unique('id');
        if ($members->isNotEmpty()) {
            Notification::send($members, new \App\Notifications\GradeNotification(
                $assignment->title,
                $course->id
            ));
        }

        return redirect()->back()->with('success', 'Nilai kelompok berhasil disimpan!');
    }

    // --- Question Management (Absorbed from QuizController) ---

    public function questions(Assignment $assignment)
    {
        // Authorization check: Use Policy instead of manual check
        $this->authorize('view', $assignment->course);

        $assignment->loadMissing('course');
        $questions = $assignment->questions()->with('options')->get();
        return view('dosen.assignments.questions.index', compact('assignment', 'questions'));
    }

    public function createQuestion(Assignment $assignment)
    {
        // Authorization check: Use Policy instead of manual check
        $this->authorize('view', $assignment->course);

        return view('dosen.assignments.questions.create', compact('assignment'));
    }

    public function storeQuestion(Request $request, Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $this->authorize('view', $assignment->course);

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
        // Load necessary relationships to check authorization
        $question->loadMissing(['assignment.course', 'options']);
        $course = $question->assignment->course;

        // Authorization check: Use Policy instead of manual check
        $this->authorize('view', $course);

        $assignment = $question->assignment;
        return view('dosen.assignments.questions.edit', compact('question', 'assignment'));
    }

    public function updateQuestion(Request $request, \App\Models\QuizQuestion $question)
    {
        $question->loadMissing('assignment.course');
        $this->authorize('view', $question->assignment->course);

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
        $question->loadMissing('assignment.course');
        $this->authorize('view', $question->assignment->course);

        $course = $question->assignment->course;

        $assignment = $question->assignment;
        $question->delete();

        return redirect()->route('dosen.assignments.questions.index', $assignment)
            ->with('success', 'Pertanyaan berhasil dihapus!');
    }
}
