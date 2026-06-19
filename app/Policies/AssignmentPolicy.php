<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;
use App\Models\Course;

// Policy hak akses tugas. Akses dosen ditentukan oleh kepemilikan matkul tempat tugas berada.
class AssignmentPolicy
{
    // Boleh melihat daftar tugas: admin atau dosen.
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    // Boleh melihat satu tugas: admin, atau dosen pengampu matkul tugas tersebut.
    public function view(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh membuat tugas: admin atau dosen.
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    // Boleh mengubah tugas: admin, atau dosen pengampu matkulnya.
    public function update(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh menghapus tugas: admin, atau dosen pengampu matkulnya.
    public function delete(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }
}
