<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Notifications\AcademicUpdateNotification;
use App\Notifications\GradeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Sends a notification when a new assignment is created for a course.
     * @param Collection<User> $users The collection of students enrolled in the course.
     * @param Assignment $assignment The newly created assignment.
     * @param Course $course The course associated with the assignment.
     */
    public function sendAssignmentCreatedNotification(Collection $users, Assignment $assignment, Course $course): void
    {
        $typeLabel = ucfirst($assignment->type);
        $message = "{$typeLabel} baru '{$assignment->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.";
        $url = route('mahasiswa.courses.show', $course); // Use actual route resolution if possible

        $users->each(function (User $user) use ($typeLabel, $message, $url) {
            Notification::send($user, new AcademicUpdateNotification(
                "{$typeLabel} Baru Ditambahkan",
                $message,
                $url
            ));
        });
    }

    /**
     * Sends a notification when an assignment is updated.
     * @param Collection<User> $users The collection of students enrolled in the course.
     * @param Assignment $assignment The updated assignment.
     * @param Course $course The course associated with the assignment.
     */
    public function sendAssignmentUpdatedNotification(Collection $users, Assignment $assignment, Course $course): void
    {
        $typeLabel = ucfirst($assignment->type);
        $message = "{$typeLabel} '{$assignment->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.";
        $url = route('mahasiswa.courses.show', $course);

        $users->each(function (User $user) use ($typeLabel, $message, $url) {
            Notification::send($user, new AcademicUpdateNotification(
                "{$typeLabel} Diperbarui",
                $message,
                $url
            ));
        });
    }

    /**
     * Sends a notification when a student's grade is recorded.
     * @param User $student The student who needs to be notified.
     * @param Assignment $assignment The assignment that was graded.
     * @param Course $course The course containing the assignment.
     */
    public function sendGradeReceivedNotification(User $student, Assignment $assignment, Course $course): void
    {
        $notification = new GradeNotification($assignment->title, $course->id);
        Notification::send($student, $notification);
    }
}