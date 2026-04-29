<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen = auth()->user();

        $courses = $dosen->courses()
            ->where('dosen_id', $dosen->id)
            ->with('semester')
            ->withCount([
                'materials',
                'assignments',
                'assignments as active_assignments_count' => fn($q) => $q->active(),
                'students',
            ])
            ->get();

        $total_students = $courses->sum('students_count');
        $total_assignments = $courses->sum('active_assignments_count');

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $total_students,
            'total_assignments' => $total_assignments,
        ];

        return view('dosen.dashboard', compact('courses', 'stats'));
    }
}
