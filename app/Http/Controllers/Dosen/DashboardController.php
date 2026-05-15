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

        $courses = $dosen->courses()
            ->with('semester', 'studentClass')
            ->withCount(['materials', 'assignments', 'students'])
            ->get();

        $courseGroups = $courses->groupBy('course_group_key')->map(function ($group) {
            return [
                'nama_matkul'       => $group->first()->nama_matkul,
                'kode_matkul'       => $group->first()->kode_matkul,
                'courses'           => $group,
                'total_students'    => $group->sum('students_count'),
                'total_materials'   => $group->sum('materials_count'),
                'total_assignments' => $group->sum('assignments_count'),
            ];
        })->values();

        $stats = [
            'total_courses'     => $courses->count(),
            'total_students'    => $courses->sum('students_count'),
            'total_assignments' => $courses->sum('assignments_count'),
        ];

        return view('dosen.dashboard', compact('courses', 'courseGroups', 'stats'));
    }
}
