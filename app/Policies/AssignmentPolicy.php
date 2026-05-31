<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;
use App\Models\Course;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    public function view(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDosen();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }
}
