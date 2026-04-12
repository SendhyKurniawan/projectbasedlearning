<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{
    /**
     * Determine whether the given user can view any courses.
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Assuming only logged-in admins/dosens should see the list,
        // otherwise this can be restricted.
        return $user->isAdmin() || $user->isDosen();
    }

    /**
     * Determine whether the given user can view the course.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Course  $course
     * @return bool
     */
    public function view(User $user, Course $course): bool
    {
        // Authorization check: only the course's owner or an admin can view.
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    /**
     * Determine whether the given user can create courses.
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Dosen can only create resources within their own course.
        // When $course is not provided (e.g. general create ability check), allow any dosen.
        if ($user->isDosen()) {
            return $course === null || $course->dosen_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the given user can update the course.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Course  $course
     * @return bool
     */
    public function update(User $user, Course $course): bool
    {
        // Same logic as viewing/managing: Admin or the course owner.
        return $user->isAdmin() || ($user->isDosen() && $user->id === $course->dosen_id);
    }

    /**
     * Determine whether the given user can delete the course.
     * @param  \App\Models\User  $user
     * @param  \App\Models\Course  $course
     * @return bool
     */
    public function delete(User $user, Course $course): bool
    {
        // Only Admin should have permission to delete a core resource like a course.
        return $user->isAdmin();
    }
}