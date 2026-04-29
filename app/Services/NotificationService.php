<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Conference;
use App\Notifications\AcademicUpdateNotification;
use App\Notifications\GradeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function sendAssignmentCreatedNotification(Collection $users, Assignment $assignment, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $typeLabel = ucfirst($assignment->type);
        Notification::send($users, new AcademicUpdateNotification(
            "{$typeLabel} Baru Ditambahkan",
            "{$typeLabel} baru '{$assignment->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
            route('mahasiswa.courses.show', $course)
        ));
    }

    public function sendAssignmentUpdatedNotification(Collection $users, Assignment $assignment, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $typeLabel = ucfirst($assignment->type);
        Notification::send($users, new AcademicUpdateNotification(
            "{$typeLabel} Diperbarui",
            "{$typeLabel} '{$assignment->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.",
            route('mahasiswa.courses.show', $course)
        ));
    }

    public function sendGradeReceivedNotification(User $student, Assignment $assignment, Course $course): void
    {
        Notification::send($student, new GradeNotification($assignment->title, $course->id));
    }

    public function sendMaterialCreatedNotification(Collection $users, Material $material, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new AcademicUpdateNotification(
            'Materi Baru Ditambahkan',
            "Materi baru '{$material->title}' telah ditambahkan pada mata kuliah {$course->nama_matkul}.",
            route('mahasiswa.materials.show', [$course, $material])
        ));
    }

    public function sendMaterialUpdatedNotification(Collection $users, Material $material, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new AcademicUpdateNotification(
            'Materi Diperbarui',
            "Materi '{$material->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.",
            route('mahasiswa.materials.show', [$course, $material])
        ));
    }

    public function sendConferenceCreatedNotification(Collection $users, Conference $conference, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new AcademicUpdateNotification(
            'Jadwal Kelas Virtual Baru',
            "Kelas virtual '{$conference->title}' telah dijadwalkan pada mata kuliah {$course->nama_matkul}.",
            route('mahasiswa.conferences.index', $course)
        ));
    }

    public function sendConferenceUpdatedNotification(Collection $users, Conference $conference, Course $course): void
    {
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new AcademicUpdateNotification(
            'Jadwal Kelas Virtual Diperbarui',
            "Jadwal kelas virtual '{$conference->title}' pada mata kuliah {$course->nama_matkul} telah diperbarui.",
            route('mahasiswa.conferences.index', $course)
        ));
    }
}
