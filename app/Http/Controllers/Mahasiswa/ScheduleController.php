<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Conference;

// Controller jadwal mahasiswa (/mahasiswa/jadwal): linimasa konferensi & tugas mendatang
// disusun hari per hari. Catatan: di sini sengaja memakai relasi Eloquent enrollments(),
// pengecualian dari konvensi query pivot langsung yang dipakai flow mahasiswa lain.
class ScheduleController extends Controller
{
    // Susun jadwal 14 hari ke depan (plus hari berikutnya yang masih ada acara).
    public function index()
    {
        $mahasiswa = auth()->user();

        $enrolledCourseIds = $mahasiswa->enrollments()->pluck('courses.id');

        $conferences = Conference::whereIn('course_id', $enrolledCourseIds)
            ->where(function ($q) {
                $q->where('status', 'live')
                    ->orWhere('scheduled_at', '>=', now()->startOfDay());
            })
            ->with(['course', 'dosen'])
            ->orderBy('scheduled_at')
            ->get();

        $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->with('course')
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now()->startOfDay());
            })
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->get();

        $submittedAssignmentIds = $assignments->isNotEmpty()
            ? $mahasiswa->submissions()
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->pluck('assignment_id')
            : collect();

        $conferencesByDate = $conferences->groupBy(
            fn ($c) => $c->scheduled_at->format('Y-m-d')
        );

        // Tugas tanpa deadline dikelompokkan ke bucket 'no-deadline'.
        $assignmentsByDate = $assignments->groupBy(
            fn ($a) => $a->deadline ? $a->deadline->format('Y-m-d') : 'no-deadline'
        );

        // Tampilkan 14 hari ke depan, ditambah hari setelahnya yang masih punya acara.
        $days = collect();
        for ($i = 0; $i < 14; $i++) {
            $days->push(now()->startOfDay()->addDays($i)->format('Y-m-d'));
        }
        $extraDays = $conferencesByDate->keys()
            ->merge($assignmentsByDate->keys()->filter(fn ($d) => $d !== 'no-deadline'))
            ->filter(fn ($d) => ! $days->contains($d))
            ->sort()
            ->values();
        $days = $days->merge($extraDays);

        return view('mahasiswa.schedule.index', compact(
            'days', 'conferencesByDate', 'assignmentsByDate', 'submittedAssignmentIds'
        ));
    }
}
