<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentStep;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\StepSubmission;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use App\Services\GroupFormationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// Pengerjaan tugas ber-step oleh mahasiswa: halaman stepper (progres step) dan
// pengumpulan hasil per step. Step terbuka berurutan; untuk tugas kelompok,
// kelompok terbentuk saat pengumpulan step pertama dan progres berbagi se-kelompok.
class AssignmentStepController extends Controller
{
    // Halaman stepper: daftar step + status (selesai/aktif/terkunci) + form step aktif.
    public function show(Assignment $assignment)
    {
        $assignment->loadMissing('course');

        if ($assignment->type !== 'tugas') {
            abort(404);
        }

        $mahasiswa = auth()->user();
        if (! $mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $steps = $assignment->steps()->get();
        if ($steps->isEmpty()) {
            // Tugas tanpa step → alur pengumpulan biasa.
            return redirect()->route('mahasiswa.submissions.create', ['assignment_id' => $assignment->id]);
        }

        $mySubmissions = StepSubmission::whereIn('assignment_step_id', $steps->pluck('id'))
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->keyBy('assignment_step_id');

        $currentStep = $steps->first(fn ($s) => ! $mySubmissions->has($s->id));

        // Submission final (mode 'final') untuk menampilkan status akhir di stepper.
        $finalSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        $existingGroup = null;
        $classmates = collect();

        if ($assignment->is_group) {
            $existingGroup = Group::where('assignment_id', $assignment->id)
                ->whereHas('members', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                ->with(['members.mahasiswa', 'creator'])
                ->first();

            if (! $existingGroup) {
                // Kandidat anggota: teman sekelas yang belum tergabung kelompok tugas ini.
                $busyMahasiswaIds = GroupMember::whereHas('group', fn ($q) => $q->where('assignment_id', $assignment->id))
                    ->pluck('mahasiswa_id')
                    ->toArray();

                $classmates = User::where('role', 'mahasiswa')
                    ->whereHas('enrollments', fn ($q) => $q->where('courses.id', $assignment->course_id))
                    ->where('id', '!=', $mahasiswa->id)
                    ->whereNotIn('id', $busyMahasiswaIds)
                    ->orderBy('name')
                    ->get(['id', 'name', 'nim']);
            }
        }

        return view('mahasiswa.assignments.steps', compact(
            'assignment', 'steps', 'mySubmissions', 'currentStep',
            'finalSubmission', 'existingGroup', 'classmates'
        ));
    }

    // Kumpulkan hasil step aktif. Kelompok terbentuk di step pertama; hasil di-fan-out
    // ke semua anggota (satu baris step_submission per anggota, pola sama Submission).
    public function submit(Request $request, Assignment $assignment, AssignmentStep $step, GroupFormationService $groupService)
    {
        if ($step->assignment_id !== $assignment->id) {
            abort(404);
        }

        $assignment->loadMissing('course');
        $mahasiswa = auth()->user();

        if (! $mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        // Hanya step aktif (semua step sebelumnya sudah dikumpulkan) yang boleh disubmit.
        $currentStep = $assignment->currentStepFor($mahasiswa->id);
        if (! $currentStep) {
            return redirect()->back()->with('error', 'Semua step sudah Anda selesaikan.');
        }
        if ($currentStep->id !== $step->id) {
            return redirect()->back()->with('error', 'Selesaikan step sebelumnya terlebih dahulu.');
        }

        $format = $step->effectiveSubmissionFormat();

        $rules = ['notes' => 'nullable|string'];
        if ($format === 'url') {
            $rules['url_link'] = 'required|url|max:2048';
        } else {
            $rules['file'] = 'required|file|max:10240';
        }

        $existingGroup = null;
        $needsNewGroup = false;

        if ($assignment->is_group) {
            $existingGroup = Group::where('assignment_id', $assignment->id)
                ->whereHas('members', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                ->with('members')
                ->first();

            if (! $existingGroup) {
                $needsNewGroup = true;
                $rules['member_ids'] = 'required|array|min:1';
                $rules['member_ids.*'] = 'integer|exists:users,id';
                $rules['group_name'] = 'nullable|string|max:120';
            }
        }

        $request->validate($rules);

        $memberIds = collect($request->member_ids ?? [])->map(fn ($id) => (int) $id)->unique();

        if ($needsNewGroup) {
            $error = $groupService->validateMembers($assignment, $mahasiswa, $memberIds);
            if ($error) {
                return redirect()->back()->withInput()->with('error', $error);
            }
        }

        $file_path = null;
        $url_link = null;

        if ($format === 'url') {
            $url_link = $request->url_link;
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time().'_'.$mahasiswa->id.'_step'.$step->step_number.'_'.$file->getClientOriginalName();
            $file_path = $file->storeAs('submissions', $filename, 'public');
        }

        // Tandai terlambat bila lewat deadline step (deadline step bersifat opsional).
        $status = ($step->deadline && now()->greaterThan($step->deadline)) ? 'late' : 'submitted';

        DB::transaction(function () use ($assignment, $step, $mahasiswa, $memberIds, $request, $file_path, $url_link, $status, $needsNewGroup, $existingGroup, $groupService) {
            $group = $existingGroup;
            if ($needsNewGroup) {
                $group = $groupService->formGroup($assignment, $mahasiswa, $memberIds, $request->group_name);
                $group->load('members');
            }

            $targetIds = $assignment->is_group
                ? $group->members->pluck('mahasiswa_id')
                : collect([$mahasiswa->id]);

            foreach ($targetIds as $mid) {
                StepSubmission::create([
                    'assignment_step_id' => $step->id,
                    'mahasiswa_id' => $mid,
                    'group_id' => $group?->id,
                    'file_path' => $file_path,
                    'url_link' => $url_link,
                    'notes' => $request->notes,
                    'submitted_at' => now(),
                    'status' => $status,
                ]);
            }
        });

        $dosen = User::find($assignment->course->dosen_id);
        if ($dosen) {
            Notification::send($dosen, new SubmissionNotification(
                $mahasiswa->name,
                "{$assignment->title} — Step {$step->step_number}: {$step->title}",
                route('dosen.assignments.submissions', $assignment)
            ));
        }

        $isLastStep = $assignment->allStepsCompletedBy($mahasiswa->id);
        $msg = "Step {$step->step_number} berhasil dikumpulkan!";
        if ($isLastStep) {
            $msg .= $assignment->step_grading_mode === 'final'
                ? ' Semua step selesai — silakan kumpulkan tugas akhir.'
                : ' Semua step selesai — menunggu penilaian dosen.';
        }

        return redirect()->route('mahasiswa.assignments.steps.show', $assignment)->with('success', $msg);
    }
}
