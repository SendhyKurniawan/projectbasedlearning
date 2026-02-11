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
        $courses = $dosen->courses()->withCount(['materials', 'assignments', 'students'])->get();
        
        $total_students = $dosen->courses()->withCount('students')->get()->sum('students_count');
        $total_assignments = Assignment::whereHas('course', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->count();

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $total_students,
            'total_assignments' => $total_assignments,
        ];

        return view('dosen.dashboard', compact('courses', 'stats'));
    }
}
