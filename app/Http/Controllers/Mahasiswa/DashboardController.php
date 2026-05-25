<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Submission;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user();

        $enrolled_courses = $mahasiswa->enrollments()
            ->withCount(['materials', 'assignments'])
            ->get();

        // Use enrolled course IDs from already-loaded collection to avoid subquery
        $enrolledCourseIds = $enrolled_courses->pluck('id');

        // Get upcoming assignments — include null-deadline and recent-past (7d) so data isn't empty
        $upcoming_assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->with('course')
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now()->subDays(7));
            })
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->take(5)
            ->get();

        // Track which assignments this mahasiswa already submitted
        $submittedAssignmentIds = $upcoming_assignments->isNotEmpty()
            ? $mahasiswa->submissions()
                ->whereIn('assignment_id', $upcoming_assignments->pluck('id'))
                ->pluck('assignment_id')
            : collect();

        // Today's conferences from enrolled courses
        $todayConferences = Conference::whereIn('course_id', $enrolledCourseIds)
            ->where(function ($q) {
                $q->where('status', 'live')
                    ->orWhereDate('scheduled_at', today());
            })
            ->with(['course', 'dosen'])
            ->orderBy('scheduled_at')
            ->get();

        $stats = [
            'enrolled_courses' => $enrolled_courses->count(),
            'total_assignments' => Assignment::whereIn('course_id', $enrolledCourseIds)->count(),
            'submitted_assignments' => $mahasiswa->submissions()->count(),
        ];

        // Get announcements
        $announcements = \App\Models\Announcement::with('author')
            ->whereIn('target_audience', ['all', 'mahasiswa'])
            ->orWhere(function ($query) use ($mahasiswa) {
                $query->where('target_audience', 'specific')
                    ->whereHas('targetedUsers', function ($q) use ($mahasiswa) {
                        $q->where('user_id', $mahasiswa->id);
                    });
            })
            ->latest()
            ->take(3)
            ->get();

        // ── 30-day personal submission activity ───────────────────────────────
        $start = now()->subDays(29)->startOfDay();
        $labels = collect(range(0, 29))->map(fn ($i) => $start->copy()->addDays($i)->format('Y-m-d'));
        $mine = Submission::where('mahasiswa_id', $mahasiswa->id)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $activitySeries = [
            'labels' => $labels->map(fn ($d) => Carbon::parse($d)->format('d M'))->all(),
            'values' => $labels->map(fn ($d) => (int) ($mine[$d] ?? 0))->all(),
        ];

        // ── Score histogram ───────────────────────────────────────────────────
        $buckets = ['0–50' => 0, '51–70' => 0, '71–85' => 0, '86–100' => 0];
        Submission::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotNull('score')
            ->pluck('score')
            ->each(function ($s) use (&$buckets) {
                $b = $s <= 50 ? '0–50' : ($s <= 70 ? '51–70' : ($s <= 85 ? '71–85' : '86–100'));
                $buckets[$b]++;
            });
        $scoreDistribution = ['labels' => array_keys($buckets), 'values' => array_values($buckets)];

        return view('mahasiswa.dashboard', compact(
            'enrolled_courses', 'upcoming_assignments', 'submittedAssignmentIds',
            'todayConferences', 'stats', 'announcements',
            'activitySeries', 'scoreDistribution'
        ));
    }
}
