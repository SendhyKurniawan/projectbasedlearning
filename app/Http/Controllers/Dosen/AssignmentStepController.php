<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentStep;
use App\Models\Group;
use App\Models\StepSubmission;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// Pengelolaan step (tahapan) tugas ber-step oleh dosen: CRUD step (terkunci begitu
// ada pengerjaan mahasiswa) dan penilaian per step untuk mode 'per_step' — nilai
// akhir diakumulasi otomatis ke tabel submissions agar rekap nilai lama tetap jalan.
class AssignmentStepController extends Controller
{
    // Halaman kelola step sebuah tugas.
    public function index(Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $this->authorize('view', $assignment->course);

        if ($assignment->type !== 'tugas') {
            abort(404);
        }

        $steps = $assignment->steps()->withCount('submissions')->get();
        $stepsLocked = $this->stepsLocked($assignment);

        return view('dosen.assignments.steps.index', compact('assignment', 'steps', 'stepsLocked'));
    }

    // Tambah step baru (nomor urut otomatis melanjutkan yang terakhir).
    public function store(Request $request, Assignment $assignment)
    {
        $assignment->loadMissing('course');
        $this->authorize('update', $assignment->course);

        if ($assignment->type !== 'tugas') {
            abort(404);
        }

        if ($this->stepsLocked($assignment)) {
            return back()->with('error', 'Step tidak dapat diubah karena sudah ada pengerjaan mahasiswa.');
        }

        $this->validateStep($request, $assignment);

        $assignment->steps()->create([
            'step_number' => ($assignment->steps()->max('step_number') ?? 0) + 1,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'submission_format' => $request->submission_format ?: null,
            'max_score' => $request->max_score,
        ]);

        return redirect()->route('dosen.assignments.steps.index', $assignment)
            ->with('success', 'Step berhasil ditambahkan!');
    }

    // Perbarui sebuah step.
    public function update(Request $request, AssignmentStep $step)
    {
        $step->loadMissing('assignment.course');
        $assignment = $step->assignment;
        $this->authorize('update', $assignment->course);

        if ($this->stepsLocked($assignment)) {
            return back()->with('error', 'Step tidak dapat diubah karena sudah ada pengerjaan mahasiswa.');
        }

        $this->validateStep($request, $assignment);

        $step->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'submission_format' => $request->submission_format ?: null,
            'max_score' => $request->max_score,
        ]);

        return redirect()->route('dosen.assignments.steps.index', $assignment)
            ->with('success', 'Step berhasil diperbarui!');
    }

    // Hapus step lalu rapatkan kembali nomor urut (1..N).
    public function destroy(AssignmentStep $step)
    {
        $step->loadMissing('assignment.course');
        $assignment = $step->assignment;
        $this->authorize('update', $assignment->course);

        if ($this->stepsLocked($assignment)) {
            return back()->with('error', 'Step tidak dapat dihapus karena sudah ada pengerjaan mahasiswa.');
        }

        DB::transaction(function () use ($step, $assignment) {
            $step->delete();

            foreach ($assignment->steps()->orderBy('step_number')->get()->values() as $i => $remaining) {
                if ($remaining->step_number !== $i + 1) {
                    $remaining->update(['step_number' => $i + 1]);
                }
            }
        });

        return redirect()->route('dosen.assignments.steps.index', $assignment)
            ->with('success', 'Step berhasil dihapus!');
    }

    // Nilai pengumpulan step SATU mahasiswa (tugas individu, mode per_step).
    public function gradeSubmission(Request $request, StepSubmission $stepSubmission)
    {
        $stepSubmission->loadMissing('step.assignment.course');
        $assignment = $stepSubmission->step->assignment;
        $this->authorize('view', $assignment->course);

        $maxScore = $stepSubmission->step->max_score ?? $assignment->max_score;
        $request->validate([
            'score' => 'required|integer|min:0|max:'.$maxScore,
            'feedback' => 'nullable|string',
        ]);

        $stepSubmission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);

        $this->syncAggregate($assignment, [$stepSubmission->mahasiswa_id]);

        return back()->with('success', 'Nilai step berhasil disimpan!');
    }

    // Nilai pengumpulan step sebuah KELOMPOK. grading_mode 'equal' → satu skor untuk
    // semua anggota; 'individual' → skor per anggota (pola sama gradeGroup tugas biasa).
    public function gradeGroup(Request $request, AssignmentStep $step, Group $group)
    {
        $step->loadMissing('assignment.course');
        $assignment = $step->assignment;
        $this->authorize('view', $assignment->course);

        if ($group->assignment_id !== $assignment->id) {
            abort(404);
        }

        $submissions = StepSubmission::where('assignment_step_id', $step->id)
            ->where('group_id', $group->id)
            ->get();

        $maxScore = $step->max_score ?? $assignment->max_score;

        if ($assignment->grading_mode === 'individual') {
            $request->validate([
                'scores' => 'required|array',
                'scores.*' => 'nullable|integer|min:0|max:'.$maxScore,
                'feedbacks' => 'nullable|array',
                'feedbacks.*' => 'nullable|string',
            ]);

            foreach ($submissions as $submission) {
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

            StepSubmission::where('assignment_step_id', $step->id)
                ->where('group_id', $group->id)
                ->update([
                    'score' => $request->score,
                    'feedback' => $request->feedback,
                    'status' => 'graded',
                ]);
        }

        $this->syncAggregate($assignment, $submissions->pluck('mahasiswa_id')->all(), $group->id);

        return back()->with('success', 'Nilai step kelompok berhasil disimpan!');
    }

    // ===== Helper =====

    // Struktur step terkunci begitu ada pengerjaan mahasiswa (jaga konsistensi data).
    private function stepsLocked(Assignment $assignment): bool
    {
        return StepSubmission::whereIn('assignment_step_id', $assignment->steps()->pluck('id'))->exists();
    }

    private function validateStep(Request $request, Assignment $assignment): void
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'submission_format' => 'nullable|in:pdf,url',
            'max_score' => ($assignment->step_grading_mode === 'per_step' ? 'required' : 'nullable').'|integer|min:1|max:100',
        ]);
    }

    /**
     * Mode 'per_step': bila SEMUA step seorang mahasiswa sudah dinilai, tulis nilai
     * akumulasi ke tabel submissions (dibuat otomatis bila belum ada) supaya rekap
     * nilai mahasiswa yang lama tetap bekerja tanpa perubahan.
     */
    private function syncAggregate(Assignment $assignment, array $mahasiswaIds, ?int $groupId = null): void
    {
        if ($assignment->step_grading_mode !== 'per_step') {
            return;
        }

        $stepIds = $assignment->steps()->pluck('id');
        $gradedMahasiswa = [];

        foreach (array_unique($mahasiswaIds) as $mahasiswaId) {
            $stepSubs = StepSubmission::whereIn('assignment_step_id', $stepIds)
                ->where('mahasiswa_id', $mahasiswaId)
                ->get();

            $allGraded = $stepSubs->count() === $stepIds->count()
                && $stepSubs->every(fn ($s) => $s->score !== null);

            if (! $allGraded) {
                continue;
            }

            Submission::updateOrCreate(
                ['assignment_id' => $assignment->id, 'mahasiswa_id' => $mahasiswaId],
                [
                    'group_id' => $groupId,
                    'score' => $stepSubs->sum('score'),
                    'status' => 'graded',
                    'submitted_at' => $stepSubs->max('submitted_at') ?? now(),
                ]
            );

            $gradedMahasiswa[] = $mahasiswaId;
        }

        if ($gradedMahasiswa !== []) {
            $students = \App\Models\User::whereIn('id', $gradedMahasiswa)->get();
            if ($students->isNotEmpty()) {
                Notification::send($students, new \App\Notifications\GradeNotification(
                    $assignment->title,
                    $assignment->course_id
                ));
            }
        }
    }
}
