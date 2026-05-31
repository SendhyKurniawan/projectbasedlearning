<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Null $course means a general create check; allow any dosen.
        if ($user->isDosen()) {
            return $course === null || $course->dosen_id === $user->id;
        }

        return false;
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
