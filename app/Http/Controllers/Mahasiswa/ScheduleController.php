<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Conference;

class ScheduleController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user();

        $enrolledCourseIds = $mahasiswa->enrollments()->pluck('courses.id');

        // Conferences: live + scheduled in next 30 days
        $conferences = Conference::whereIn('course_id', $enrolledCourseIds)
            ->where(function ($q) {
                $q->where('status', 'live')
                    ->orWhere('scheduled_at', '>=', now()->startOfDay());
            })
            ->with(['course', 'dosen'])
            ->orderBy('scheduled_at')
            ->get();

        // Upcoming assignments with null-deadline tolerance
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

        // Group conferences by date string for easy day-by-day rendering
        $conferencesByDate = $conferences->groupBy(
            fn ($c) => $c->scheduled_at->format('Y-m-d')
        );

        // Group assignments by deadline date (null-deadline goes to 'no-deadline' bucket)
        $assignmentsByDate = $assignments->groupBy(
            fn ($a) => $a->deadline ? $a->deadline->format('Y-m-d') : 'no-deadline'
        );

        // Build ordered day list: next 14 days + any beyond that with events
        $days = collect();
        for ($i = 0; $i < 14; $i++) {
            $days->push(now()->startOfDay()->addDays($i)->format('Y-m-d'));
        }
        // Add extra days that have conferences/assignments beyond 14 days
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
