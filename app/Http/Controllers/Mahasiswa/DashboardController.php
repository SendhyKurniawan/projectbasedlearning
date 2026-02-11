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
        $enrolled_courses = $mahasiswa->enrolledCourses()->withCount(['materials', 'assignments'])->get();
        
        // Get upcoming assignments
        $upcoming_assignments = Assignment::whereHas('course.students', function($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id);
        })
        ->where('deadline', '>=', now())
        ->orderBy('deadline')
        ->take(5)
        ->get();

        $stats = [
            'enrolled_courses' => $enrolled_courses->count(),
            'total_assignments' => Assignment::whereHas('course.students', function($q) use ($mahasiswa) {
                $q->where('mahasiswa_id', $mahasiswa->id);
            })->count(),
            'submitted_assignments' => $mahasiswa->submissions()->count(),
        ];

        return view('mahasiswa.dashboard', compact('enrolled_courses', 'upcoming_assignments', 'stats'));
    }
}
