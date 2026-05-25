<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;
use App\Models\Course;

class AssignmentPolicy
{
    /**
     * Determine whether the given user can view any assignments.
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Assuming any Dosen can view the list of assignments associated with courses they own.
        return $user->isAdmin() || $user->isDosen();
    }

    /**
     * Determine whether the given user can view the assignment.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assignment  $assignment
     * @return bool
     */
    public function view(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        // Authorization check: only the course's owner or an admin can view.
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    /**
     * Determine whether the given user can create an assignment.
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only Dosen or Admin should be able to create assignments.
        return $user->isAdmin() || $user->isDosen();
    }

    /**
     * Determine whether the given user can update the assignment.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assignment  $assignment
     * @return bool
     */
    public function update(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        // Authorization check: Admin or the course owner.
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    /**
     * Determine whether the given user can delete the assignment.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assignment  $assignment
     * @return bool
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }
}