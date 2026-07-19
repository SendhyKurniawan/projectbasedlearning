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

// Controller utama pengelolaan tugas oleh dosen: CRUD tugas (tugas/quiz/exercise),
// fan-out ke kelas siblings, atur urutan, lihat & nilai submission, dan kelola soal quiz.
class AssignmentController extends Controller
{
    // Daftar tugas sebuah matkul (beserta jumlah submission) + siblings untuk fan-out.
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $assignments = $course->assignments()
            ->withCount('submissions')
            ->orderBy('order')
            ->orderBy('deadline', 'desc')
            ->get();

        $siblings = $course->siblings();

        return view('dosen.assignments.index', compact('course', 'assignments', 'siblings'));
    }

    // Form buat tugas (materi untuk prasyarat + siblings untuk fan-out).
    public function create(Course $course)
    {
        $this->authorize('create', $course);

        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);
        $siblings = $course->siblings();

        return view('dosen.assignments.create', compact('course', 'materials', 'siblings'));
    }

    // Simpan tugas baru. Quiz hanya dibuatkan "shell" lalu diarahkan ke pengisian soal;
    // tugas/exercise langsung jadi + notifikasi mahasiswa, lalu fan-out ke siblings.
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
            'has_steps' => 'nullable|in:0,1,true,false',
            'step_grading_mode' => 'nullable|in:final,per_step',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        // Keamanan fan-out: batasi target hanya ke siblings milik matkul ini.
        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        // Mode kelompok & step progresi hanya berlaku untuk tipe 'tugas'.
        $isGroup = $request->type === 'tugas' && $request->boolean('is_group');
        $hasSteps = $request->type === 'tugas' && $request->boolean('has_steps');

        $sharedData = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'type' => $request->type,
            'submission_format' => $request->type === 'tugas' ? $request->submission_format : 'pdf',
            'duration_minutes' => $request->has_duration ? $request->duration_minutes : null,
            'is_group' => $isGroup,
            'max_group_size' => $isGroup ? $request->max_group_size : null,
            'grading_mode' => $isGroup ? ($request->grading_mode ?: 'equal') : 'equal',
            'step_grading_mode' => $hasSteps ? ($request->step_grading_mode ?: 'final') : 'final',
        ];

        // Closure pembuat tugas untuk satu matkul: hitung nomor urut (quiz/tugas) & order.
        $createForCourse = function (Course $target) use ($sharedData) {
            $count = Assignment::where('course_id', $target->id)->where('type', $sharedData['type'])->count();
            $maxOrder = $target->assignments()->max('order') ?? 0;

            return Assignment::create(array_merge($sharedData, [
                'course_id' => $target->id,
                'quiz_number' => $sharedData['type'] === 'quiz' ? $count + 1 : null,
                'assignment_number' => $sharedData['type'] !== 'quiz' ? $count + 1 : null,
                'order' => $maxOrder + 1,
            ]));
        };

        $assignment = $createForCourse($course);

        if ($request->type === 'quiz') {
            $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
            foreach ($targetCourses as $sibling) {
                $createForCourse($sibling);
            }
            $msg = 'Quiz berhasil dibuat! Silakan tambahkan pertanyaan.';
            if ($targetIds->count()) {
                $msg .= " Shell quiz dibuat di {$targetIds->count()} kelas lain — tambahkan pertanyaan secara terpisah.";
            }

            return redirect()->route('dosen.assignments.questions.index', $assignment)->with('success', $msg);
        }

        // Tugas ber-step: buat tugas dulu lalu arahkan dosen mengisi step (pola shell quiz).
        if ($hasSteps) {
            $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
            foreach ($targetCourses as $sibling) {
                $createForCourse($sibling);
            }
            $msg = 'Tugas berhasil dibuat! Silakan tambahkan step pengerjaan.';
            if ($targetIds->count()) {
                $msg .= " Salinan tugas dibuat di {$targetIds->count()} kelas lain — tambahkan step di sana secara terpisah, atau gunakan menu Salin setelah step lengkap.";
            }

            return redirect()->route('dosen.assignments.steps.index', $assignment)->with('success', $msg);
        }

        $typeLabel = ucfirst($request->type);
        $students = User::whereHas('enrollments', fn ($q) => $q->where('course_id', $course->id))->get();
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                "{$typeLabel} Baru Ditambahkan",
                "{$typeLabel} baru '{$assignment->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.courses.show', $course)
            ));
        }

        // Fan-out: buat tugas serupa + notifikasi untuk tiap kelas sibling terpilih.
        $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
        foreach ($targetCourses as $sibling) {
            $sibAssignment = $createForCourse($sibling);
            $sibStudents = User::whereHas('enrollments', fn ($q) => $q->where('course_id', $sibling->id))->get();
            if ($sibStudents->isNotEmpty()) {
                Notification::send($sibStudents, new AcademicUpdateNotification(
                    "{$typeLabel} Baru Ditambahkan",
                    "{$typeLabel} baru '{$sibAssignment->title}' telah ditambahkan pada mata kuliah {$sibling->nama_matkul}.",
                    route('mahasiswa.courses.show', $sibling)
                ));
            }
        }

        $msg = 'Berhasil ditambahkan!';
        if ($targetIds->count()) {
            $msg .= " Disalin ke {$targetIds->count()} kelas lain.";
        }

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', $msg);
    }

    // Form edit tugas.
    public function edit(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;

        $this->authorize('view', $course);

        $materials = $course->materials()->orderBy('order')->get(['id', 'title']);

        return view('dosen.assignments.edit', compact('assignment', 'course', 'materials'));
    }

    // Perbarui tugas & beri tahu mahasiswa (pengaturan kelompok dikunci bila sudah ada submission).
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
            'step_grading_mode' => 'nullable|in:final,per_step',
        ]);

        $data = $request->only([
            'title', 'description', 'deadline', 'max_score', 'type',
        ]);
        $data['duration_minutes'] = $request->has_duration ? $request->duration_minutes : null;
        $data['submission_format'] = $request->type === 'tugas' ? $request->submission_format : 'pdf';

        $isGroup = $request->type === 'tugas' && $request->boolean('is_group');
        $hasSubmission = $assignment->submissions()->exists()
            || \App\Models\StepSubmission::whereIn('assignment_step_id', $assignment->steps()->pluck('id'))->exists();

        // Kunci pengaturan kelompok & mode penilaian step begitu sudah ada pengerjaan.
        if ($hasSubmission) {
            $data['is_group'] = $assignment->is_group;
            $data['max_group_size'] = $assignment->max_group_size;
            $data['grading_mode'] = $assignment->grading_mode;
            $data['step_grading_mode'] = $assignment->step_grading_mode;
        } else {
            $data['is_group'] = $isGroup;
            $data['max_group_size'] = $isGroup ? $request->max_group_size : null;
            $data['grading_mode'] = $isGroup ? ($request->grading_mode ?: 'equal') : 'equal';
            $data['step_grading_mode'] = $request->step_grading_mode ?: $assignment->step_grading_mode;
        }

        $assignment->update($data);

        $students = User::whereHas('enrollments', function ($q) use ($course) {
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

    // Hapus tugas.
    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->loadMissing('course');
        $course = $assignment->course;

        $assignment->delete();

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Berhasil dihapus!');
    }

    // Salin satu tugas ke beberapa kelas siblings (quiz disalin tanpa soal — shell saja).
    public function copy(Request $request, Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;
        $this->authorize('update', $course);

        $request->validate([
            'sibling_ids' => 'required|array|min:1',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        // Batasi target ke siblings milik matkul ini saja.
        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids)
            ->map(fn ($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        if ($targetIds->isEmpty()) {
            return back()->with('error', 'Pilih kelas tujuan yang valid.');
        }

        $sharedData = $assignment->only([
            'title', 'description', 'deadline', 'max_score', 'type',
            'submission_format', 'duration_minutes', 'is_group',
            'max_group_size', 'grading_mode', 'exercise_config',
            'step_grading_mode',
        ]);

        $steps = $assignment->steps()->get();

        $targetCourses = Course::whereIn('id', $targetIds)->get();
        foreach ($targetCourses as $sibling) {
            $count = Assignment::where('course_id', $sibling->id)->where('type', $sharedData['type'])->count();
            $maxOrder = $sibling->assignments()->max('order') ?? 0;
            $newAssignment = Assignment::create(array_merge($sharedData, [
                'course_id' => $sibling->id,
                'quiz_number' => $sharedData['type'] === 'quiz' ? $count + 1 : null,
                'assignment_number' => $sharedData['type'] !== 'quiz' ? $count + 1 : null,
                'order' => $maxOrder + 1,
            ]));

            // Salin step progresi secara penuh (beda dengan soal quiz yang shell-only).
            foreach ($steps as $step) {
                $newAssignment->steps()->create($step->only([
                    'step_number', 'title', 'description', 'deadline',
                    'submission_format', 'max_score',
                ]));
            }
        }

        $msg = "'{$assignment->title}' disalin ke {$targetIds->count()} kelas lain.";
        if ($assignment->type === 'quiz') {
            $msg .= ' Tambahkan pertanyaan secara terpisah di kelas tujuan.';
        }

        return back()->with('success', $msg);
    }

    // Simpan urutan baru tugas hasil drag-and-drop (normalisasi nilai 'order' agar tetap naik).
    public function reorder(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:assignments,id',
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

    // Halaman daftar submission/pengerjaan. Quiz → tampilan percobaan; tugas kelompok → muat grup.
    public function submissions(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;

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

        // Tugas ber-step: muat step + pengumpulan per step untuk matriks progres & penilaian.
        $steps = $assignment->steps()->with(['submissions.mahasiswa', 'submissions.group'])->get();

        return view('dosen.assignments.submissions', compact('assignment', 'course', 'submissions', 'groups', 'steps'));
    }

    // Lihat detail satu percobaan quiz seorang mahasiswa (jawaban per soal).
    public function showQuizAttempt(Assignment $assignment, Submission $submission)
    {
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

    // Beri nilai satu submission individu + kirim notifikasi nilai ke mahasiswa.
    public function grade(Request $request, Submission $submission)
    {
        $submission->loadMissing(['assignment.course']);
        $assignment = $submission->assignment;
        $course = $assignment->course;

        $this->authorize('view', $course);

        $request->validate([
            'score' => 'required|integer|min:0|max:'.$assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);

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

    // Beri nilai tugas kelompok. Mode 'individual' → skor per anggota; 'equal' → skor sama untuk semua.
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
                'scores.*' => 'nullable|integer|min:0|max:'.$maxScore,
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
                'score' => 'required|integer|min:0|max:'.$maxScore,
                'feedback' => 'nullable|string',
            ]);

            Submission::where('group_id', $group->id)->update([
                'score' => $request->score,
                'feedback' => $request->feedback,
                'status' => 'graded',
            ]);
        }

        $members = $group->submissions->pluck('mahasiswa')->filter()->unique('id');
        if ($members->isNotEmpty()) {
            Notification::send($members, new \App\Notifications\GradeNotification(
                $assignment->title,
                $course->id
            ));
        }

        return redirect()->back()->with('success', 'Nilai kelompok berhasil disimpan!');
    }

    // ===== Pengelolaan soal quiz =====

    // Daftar soal sebuah quiz.
    public function questions(Assignment $assignment)
    {
        $this->authorize('view', $assignment->course);

        $assignment->loadMissing('course');
        $questions = $assignment->questions()->with('options')->get();

        return view('dosen.assignments.questions.index', compact('assignment', 'questions'));
    }

    // Form tambah soal.
    public function createQuestion(Assignment $assignment)
    {
        $this->authorize('view', $assignment->course);

        return view('dosen.assignments.questions.create', compact('assignment'));
    }

    // Simpan soal baru; untuk pilihan ganda, simpan opsi-opsinya dalam satu transaksi.
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

    // Form edit soal.
    public function editQuestion(\App\Models\QuizQuestion $question)
    {
        $question->loadMissing(['assignment.course', 'options']);
        $course = $question->assignment->course;

        $this->authorize('view', $course);

        $assignment = $question->assignment;

        return view('dosen.assignments.questions.edit', compact('question', 'assignment'));
    }

    // Perbarui soal; untuk pilihan ganda, opsi lama dihapus lalu ditulis ulang (dalam transaksi).
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

    // Hapus soal.
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
