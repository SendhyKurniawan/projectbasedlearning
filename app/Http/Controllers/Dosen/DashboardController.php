<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen = auth()->user();

        // Load all needed counts in a single query per relationship
        $courses = $dosen->courses()
            ->with('semester')
            ->withCount(['materials', 'assignments', 'students'])
            ->get();

        // Reuse the already-loaded collection instead of doing a second DB query
        $total_students = $courses->sum('students_count');
        $total_assignments = $courses->sum('assignments_count');

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $total_students,
            'total_assignments' => $total_assignments,
        ];

        return view('dosen.dashboard', compact('courses', 'stats'));
    }
}
