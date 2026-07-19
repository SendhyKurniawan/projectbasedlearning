<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

// Controller pengumpulan tugas oleh mahasiswa: CRUD submission, baik tugas individu
// maupun kelompok (pembentukan kelompok + pengumpulan untuk semua anggota sekaligus).
class SubmissionController extends Controller
{
    // Form pengumpulan. Untuk tugas kelompok: tampilkan kelompok yang ada atau kandidat anggota.
    public function create(Request $request)
    {
        $assignment_id = $request->query('assignment_id');
        $assignment = Assignment::with('course')->findOrFail($assignment_id);

        $mahasiswa = auth()->user();
        if (! $mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        // Tugas ber-step: pengumpulan final hanya untuk mode 'final' setelah semua step
        // selesai; mode 'per_step' dinilai per step (tidak ada pengumpulan final).
        if ($assignment->hasSteps()) {
            if ($assignment->step_grading_mode === 'per_step') {
                return redirect()->route('mahasiswa.assignments.steps.show', $assignment);
            }
            if (! $assignment->allStepsCompletedBy($mahasiswa->id)) {
                return redirect()->route('mahasiswa.assignments.steps.show', $assignment)
                    ->with('error', 'Selesaikan semua step terlebih dahulu sebelum mengumpulkan tugas akhir.');
            }
        }

        $existing = Submission::where('assignment_id', $assignment_id)
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
                // Kandidat anggota tidak termasuk diri sendiri & mahasiswa yang sudah masuk kelompok pada tugas ini.
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

        return view('mahasiswa.submissions.create', compact('assignment', 'existing', 'existingGroup', 'classmates'));
    }

    // Simpan pengumpulan. Aturan validasi menyesuaikan format (file/url) & mode (individu/kelompok).
    // Untuk kelompok: bentuk Group + GroupMember + satu Submission per anggota (dalam transaksi).
    public function store(Request $request, \App\Services\GroupFormationService $groupService)
    {
        $assignment = Assignment::findOrFail($request->assignment_id);

        $mahasiswa = auth()->user();

        // Tugas ber-step: pengumpulan final hanya untuk mode 'final' setelah semua step selesai.
        if ($assignment->hasSteps()) {
            if ($assignment->step_grading_mode === 'per_step') {
                return redirect()->route('mahasiswa.assignments.steps.show', $assignment);
            }
            if (! $assignment->allStepsCompletedBy($mahasiswa->id)) {
                return redirect()->route('mahasiswa.assignments.steps.show', $assignment)
                    ->with('error', 'Selesaikan semua step terlebih dahulu sebelum mengumpulkan tugas akhir.');
            }
        }

        // Kelompok bisa sudah terbentuk lebih dulu (saat step pertama tugas ber-step).
        $existingGroup = null;
        if ($assignment->is_group) {
            $existingGroup = Group::where('assignment_id', $assignment->id)
                ->whereHas('members', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                ->with('members')
                ->first();
        }

        $rules = [
            'assignment_id' => 'required|exists:assignments,id',
            'notes' => 'nullable|string',
        ];

        if ($assignment->submission_format === 'url') {
            $rules['url_link'] = 'required|url|max:2048';
        } else {
            $rules['file'] = 'required|file|max:10240';
        }

        // Pemilihan anggota hanya saat kelompok belum terbentuk.
        if ($assignment->is_group && ! $existingGroup) {
            $rules['member_ids'] = 'required|array|min:1';
            $rules['member_ids.*'] = 'integer|exists:users,id';
            $rules['group_name'] = 'nullable|string|max:120';
        }

        $request->validate($rules);

        if (! $mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }

        $file_path = null;
        $url_link = null;

        if ($assignment->submission_format === 'url') {
            $url_link = $request->url_link;
        } else {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time().'_'.$mahasiswa->id.'_'.$file->getClientOriginalName();
                $file_path = $file->storeAs('submissions', $filename, 'public');
            }
        }

        if ($assignment->is_group) {
            $memberIds = collect($request->member_ids ?? [])->map(fn ($id) => (int) $id)->unique();

            if (! $existingGroup) {
                $error = $groupService->validateMembers($assignment, $mahasiswa, $memberIds);
                if ($error) {
                    return redirect()->back()->withInput()->with('error', $error);
                }
            }

            DB::transaction(function () use ($assignment, $mahasiswa, $memberIds, $request, $file_path, $url_link, $existingGroup, $groupService) {
                // Pakai kelompok yang sudah terbentuk (tugas ber-step) atau bentuk baru via service.
                $group = $existingGroup ?: $groupService->formGroup($assignment, $mahasiswa, $memberIds, $request->group_name);
                $group->loadMissing('members');

                foreach ($group->members->pluck('mahasiswa_id') as $mid) {
                    Submission::create([
                        'assignment_id' => $assignment->id,
                        'mahasiswa_id' => $mid,
                        'group_id' => $group->id,
                        'file_path' => $file_path,
                        'url_link' => $url_link,
                        'notes' => $request->notes,
                        'submitted_at' => now(),
                    ]);
                }
            });
        } else {
            Submission::create([
                'assignment_id' => $assignment->id,
                'mahasiswa_id' => $mahasiswa->id,
                'file_path' => $file_path,
                'url_link' => $url_link,
                'notes' => $request->notes,
                'submitted_at' => now(),
            ]);
        }

        $dosen = User::find($assignment->course->dosen_id);
        if ($dosen) {
            Notification::send($dosen, new SubmissionNotification(
                $mahasiswa->name,
                $assignment->title,
                route('dosen.assignments.submissions', $assignment)
            ));
        }

        return redirect()->route('mahasiswa.courses.show', $assignment->course_id)
            ->with('success', 'Tugas berhasil dikumpulkan!');
    }

    // Form edit pengumpulan. Hanya pemilik; tugas yang sudah dinilai tidak boleh diubah;
    // untuk kelompok hanya pembuat kelompok yang boleh mengedit.
    public function edit(Submission $submission)
    {
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }

        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat diubah.');
        }

        if ($submission->group_id) {
            $submission->loadMissing('group');
            if ($submission->group?->created_by_mahasiswa_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Hanya pembuat kelompok yang dapat mengedit pengumpulan kelompok.');
            }
        }

        $assignment = $submission->assignment()->with('course')->first();

        return view('mahasiswa.submissions.edit', compact('submission', 'assignment'));
    }

    // Perbarui pengumpulan (ganti berkas/url/catatan). Untuk kelompok, perubahan disalin ke semua anggota.
    public function update(Request $request, Submission $submission)
    {
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }

        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat diubah.');
        }

        if ($submission->group_id) {
            $submission->loadMissing('group');
            if ($submission->group?->created_by_mahasiswa_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Hanya pembuat kelompok yang dapat mengubah pengumpulan kelompok.');
            }
        }

        $rules = [
            'notes' => 'nullable|string',
        ];

        if ($submission->assignment->submission_format === 'url') {
            $rules['url_link'] = 'nullable|url|max:2048';
        } else {
            $rules['file'] = 'nullable|file|max:10240';
        }

        $request->validate($rules);

        $data = ['notes' => $request->notes];

        if ($submission->assignment->submission_format === 'url') {
            if ($request->filled('url_link')) {
                $data['url_link'] = $request->url_link;
            }
        } else {
            if ($request->hasFile('file')) {
                if ($submission->file_path) {
                    Storage::disk('public')->delete($submission->file_path);
                }

                $file = $request->file('file');
                $filename = time().'_'.auth()->id().'_'.$file->getClientOriginalName();
                $data['file_path'] = $file->storeAs('submissions', $filename, 'public');
            }
        }

        if ($submission->group_id) {
            // Salin berkas/url/catatan ke baris submission semua anggota kelompok.
            Submission::where('group_id', $submission->group_id)->update($data);
        } else {
            $submission->update($data);
        }

        return redirect()->route('mahasiswa.courses.show', $submission->assignment->course_id)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    // Hapus pengumpulan (beserta berkasnya). Untuk kelompok, hapus seluruh data kelompok terkait.
    public function destroy(Submission $submission)
    {
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }

        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat dihapus.');
        }

        $course_id = $submission->assignment->course_id;

        if ($submission->group_id) {
            $group = $submission->group;
            if ($group->created_by_mahasiswa_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Hanya pembuat kelompok yang dapat menghapus pengumpulan ini.');
            }
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            Submission::where('group_id', $group->id)->delete();
            GroupMember::where('group_id', $group->id)->delete();
            $group->delete();
        } else {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->delete();
        }

        return redirect()->route('mahasiswa.courses.show', $course_id)
            ->with('success', 'Tugas berhasil dihapus!');
    }
}
