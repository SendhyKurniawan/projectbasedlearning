<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

// Pembentukan kelompok untuk tugas kelompok. Dipakai oleh dua alur:
// pengumpulan tugas biasa (SubmissionController) dan pengumpulan step pertama
// tugas ber-step (Mahasiswa\AssignmentStepController).
class GroupFormationService
{
    /**
     * Validasi calon anggota kelompok. Mengembalikan pesan error (string) bila
     * tidak valid, atau null bila lolos semua pemeriksaan.
     *
     * @param  Collection<int, int>  $memberIds  id anggota selain si pembuat
     */
    public function validateMembers(Assignment $assignment, User $mahasiswa, Collection $memberIds): ?string
    {
        $invalidEnroll = User::whereIn('id', $memberIds)
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('courses.id', $assignment->course_id))
            ->exists();
        if ($invalidEnroll) {
            return 'Beberapa anggota yang dipilih tidak terdaftar pada course ini.';
        }

        $alreadyMember = GroupMember::whereHas('group', fn ($q) => $q->where('assignment_id', $assignment->id))
            ->whereIn('mahasiswa_id', $memberIds->concat([$mahasiswa->id]))
            ->exists();
        if ($alreadyMember) {
            return 'Anda atau salah satu anggota sudah tergabung di kelompok lain untuk tugas ini.';
        }

        // max_group_size sudah termasuk si pembuat kelompok.
        if ($assignment->max_group_size && ($memberIds->count() + 1) > $assignment->max_group_size) {
            return 'Jumlah anggota melebihi batas maksimal kelompok.';
        }

        return null;
    }

    /**
     * Bentuk kelompok beserta anggotanya dan kirim notifikasi ke anggota lain.
     * Panggil di dalam transaksi milik pemanggil.
     *
     * @param  Collection<int, int>  $memberIds  id anggota selain si pembuat
     */
    public function formGroup(Assignment $assignment, User $mahasiswa, Collection $memberIds, ?string $groupName): Group
    {
        $groupName = $groupName ?: 'Kelompok '.($assignment->groups()->count() + 1);

        $group = Group::create([
            'assignment_id' => $assignment->id,
            'group_name' => $groupName,
            'created_by_mahasiswa_id' => $mahasiswa->id,
        ]);

        $allMemberIds = $memberIds->reject(fn ($id) => $id === $mahasiswa->id)->push($mahasiswa->id)->unique();

        foreach ($allMemberIds as $mid) {
            GroupMember::create([
                'group_id' => $group->id,
                'mahasiswa_id' => $mid,
            ]);
        }

        $others = User::whereIn('id', $allMemberIds->reject(fn ($id) => $id === $mahasiswa->id))->get();
        if ($others->isNotEmpty()) {
            Notification::send($others, new AcademicUpdateNotification(
                'Ditambahkan ke Kelompok',
                "{$mahasiswa->name} menambahkan Anda ke kelompok '{$groupName}' untuk tugas '{$assignment->title}'.",
                route('mahasiswa.courses.show', $assignment->course_id)
            ));
        }

        return $group;
    }
}
