<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\QuizQuestion;
use App\Models\Submission;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Course $course)
    {
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
        $this->authorize('update', $course);

        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);

        return view('dosen.assignments.create', compact('course', 'materials'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $type = $request->input('type');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|in:0,1,true,false',
            'submission_format' => $type === 'tugas' ? 'required|in:pdf,url' : 'nullable',
            'duration_minutes' => 'nullable|integer|min:1',
            'required_material_id' => [
                'nullable',
                Rule::exists('materials', 'id')->where('course_id', $course->id),
            ],
        ]);

        $count = Assignment::where('course_id', $course->id)
            ->where('type', $request->type)
            ->count();
        $nextNumber = $count + 1;

        $maxOrder = $course->assignments()->max('order') ?? 0;

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
            'required_material_id' => $request->required_material_id ?: null,
            'order' => $maxOrder + 1,
        ]);

        if ($request->type === 'quiz') {
            return redirect()->route('dosen.assignments.questions.index', $assignment)
                ->with('success', 'Quiz berhasil dibuat! Silakan tambahkan pertanyaan.');
        }

        $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
        $this->notifications->sendAssignmentCreatedNotification($students, $assignment, $course);

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil ditambahkan!');
    }

    public function edit(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $this->authorize('update', $assignment);

        $course = $assignment->course;
        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);

        return view('dosen.assignments.edit', compact('assignment', 'course', 'materials'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $assignment->loadMissing('course');
        $course = $assignment->course;

        $type = $request->input('type', $assignment->type);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
            'type' => 'required|in:tugas,quiz,exercise',
            'has_duration' => 'nullable|in:0,1,true,false',
            'submission_format' => $type === 'tugas' ? 'required|in:pdf,url' : 'nullable',
            'duration_minutes' => 'nullable|integer|min:1',
            'required_material_id' => [
                'nullable',
                Rule::exists('materials', 'id')->where('course_id', $course->id),
            ],
        ]);

        $data = $request->only([
            'title', 'description', 'deadline', 'max_score', 'type'
        ]);
        $data['duration_minutes'] = $request->has_duration ? $request->duration_minutes : null;
        $data['submission_format'] = $request->type === 'tugas' ? $request->submission_format : 'pdf';
        $data['required_material_id'] = $request->required_material_id ?: null;

        $assignment->update($data);

        if ($assignment->wasChanged(['title', 'deadline'])) {
            $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
            $this->notifications->sendAssignmentUpdatedNotification($students, $assignment, $course);
        }

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil diperbarui!');
    }

    public function destroy(Assignment $assignment)
    {
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
            'ordered_ids.*' => [
                'integer',
                Rule::exists('assignments', 'id')->where('course_id', $course->id),
            ],
        ]);

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
        $this->authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->with('mahasiswa')
            ->orderBy($assignment->type === 'quiz' ? 'finished_at' : 'submitted_at', 'desc')
            ->get();

        if ($assignment->type === 'quiz') {
            return view('dosen.assignments.quiz_attempts', compact('assignment', 'submissions'))
                ->with('course', $assignment->course);
        }

        return view('dosen.assignments.submissions', compact('assignment', 'submissions'))
            ->with('course', $assignment->course);
    }

    public function showQuizAttempt(Assignment $assignment, Submission $submission)
    {
        $assignment->loadMissing('course');
        $this->authorize('view', $assignment);

        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $assignment->load('questions.options');

        return view('dosen.assignments.quiz_attempt_show', compact('assignment', 'submission'));
    }

    public function grade(Request $request, Submission $submission)
    {
        $submission->loadMissing(['assignment.course']);
        $assignment = $submission->assignment;
        $course = $assignment->course;

        $this->authorize('update', $assignment);

        $request->validate([
            'score' => 'required|integer|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $submission) {
            $submission->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'status' => 'graded',
            ]);
        });

        $student = $submission->mahasiswa;
        if ($student) {
            $this->notifications->sendGradeReceivedNotification($student, $assignment, $course);
        }

        return redirect()->back()
            ->with('success', 'Nilai berhasil diberikan!');
    }

    // --- Question Management ---

    public function questions(Assignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->loadMissing('course');
        $questions = $assignment->questions()->with('options')->get();

        return view('dosen.assignments.questions.index', compact('assignment', 'questions'));
    }

    public function createQuestion(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        return view('dosen.assignments.questions.create', compact('assignment'));
    }

    public function storeQuestion(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:essay,pilihan_ganda,code_snippet',
            'score_weight' => 'required|integer|min:1',
            'correct_answer' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.text' => 'required_with:options|string',
        ]);

        DB::transaction(function () use ($request, $assignment) {
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

    public function editQuestion(QuizQuestion $question)
    {
        $question->loadMissing(['assignment.course', 'options']);
        $this->authorize('update', $question->assignment);

        $assignment = $question->assignment;

        return view('dosen.assignments.questions.edit', compact('question', 'assignment'));
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
        $question->loadMissing('assignment.course');
        $this->authorize('update', $question->assignment);

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

    public function destroyQuestion(QuizQuestion $question)
    {
        $question->loadMissing('assignment.course');
        $this->authorize('update', $question->assignment);

        $assignment = $question->assignment;
        $question->delete();

        return redirect()->route('dosen.assignments.questions.index', $assignment)
            ->with('success', 'Pertanyaan berhasil dihapus!');
    }
}
