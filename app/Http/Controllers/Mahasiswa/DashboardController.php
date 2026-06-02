<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user();

        $enrolled_courses = $mahasiswa->enrollments()
            ->withCount(['materials', 'assignments'])
            ->get();

        $enrolledCourseIds = $enrolled_courses->pluck('id');

        // Real per-course progress = (viewed materials + submitted assignments) / total items.
        // Mirrors the learning-path completion used on the course detail page
        // (CourseController::buildLearningPath). The old dashboard formula was
        // assignments/(materials+assignments) — a static ratio of course content that
        // ignored the student entirely (e.g. a 5-assignment/2-material course always read 71%).
        $viewedMaterialsByCourse = DB::table('material_views')
            ->join('materials', 'materials.id', '=', 'material_views.material_id')
            ->where('material_views.student_id', $mahasiswa->id)
            ->whereIn('materials.course_id', $enrolledCourseIds)
            ->groupBy('materials.course_id')
            ->selectRaw('materials.course_id as cid, COUNT(DISTINCT material_views.material_id) as c')
            ->pluck('c', 'cid');

        $submittedAssignmentsByCourse = DB::table('submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('submissions.mahasiswa_id', $mahasiswa->id)
            ->whereIn('assignments.course_id', $enrolledCourseIds)
            ->groupBy('assignments.course_id')
            ->selectRaw('assignments.course_id as cid, COUNT(DISTINCT submissions.assignment_id) as c')
            ->pluck('c', 'cid');

        $courseProgress = $enrolled_courses->mapWithKeys(function ($c) use ($viewedMaterialsByCourse, $submittedAssignmentsByCourse) {
            $total = ($c->materials_count ?? 0) + ($c->assignments_count ?? 0);
            $done = ($viewedMaterialsByCourse[$c->id] ?? 0) + ($submittedAssignmentsByCourse[$c->id] ?? 0);

            return [$c->id => $total > 0 ? (int) round($done / $total * 100) : 0];
        });

        // Include null-deadline and recent-past (7d) so the panel isn't empty.
        $upcoming_assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->with('course')
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now()->subDays(7));
            })
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->take(5)
            ->get();

        $submittedAssignmentIds = $upcoming_assignments->isNotEmpty()
            ? $mahasiswa->submissions()
                ->whereIn('assignment_id', $upcoming_assignments->pluck('id'))
                ->pluck('assignment_id')
            : collect();

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

        // 30-day submission activity series
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

        // Score histogram
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
            'activitySeries', 'scoreDistribution', 'courseProgress'
        ));
    }
}
