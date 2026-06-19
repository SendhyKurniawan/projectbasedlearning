<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

// Policy hak akses mata kuliah. Pola umum: admin boleh semua; dosen hanya untuk matkul miliknya.
class CoursePolicy
{
    // Boleh melihat daftar matkul: admin atau dosen.
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    // Boleh melihat satu matkul: admin, atau dosen pengampu matkul tersebut.
    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh membuat: admin selalu boleh; dosen boleh (cek matkul bila konteksnya ada).
    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // $course null berarti pengecekan create umum; izinkan dosen mana pun.
        if ($user->isDosen()) {
            return $course === null || $course->dosen_id === $user->id;
        }

        return false;
    }

    // Boleh mengubah: admin, atau dosen pengampu matkul tersebut.
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    // Boleh menghapus: hanya admin.
    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
