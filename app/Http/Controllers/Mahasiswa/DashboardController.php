<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;

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

        // Get upcoming assignments using direct IN clause (faster than nested whereHas)
        $upcoming_assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->with('course')
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->take(5)
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

        return view('mahasiswa.dashboard', compact('enrolled_courses', 'upcoming_assignments', 'stats', 'announcements'));
    }
}
