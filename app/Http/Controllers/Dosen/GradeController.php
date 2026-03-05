<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = Auth::id();
        
        $search = $request->input('search');
        $semesterId = $request->input('semester_id');
        $academicYearId = $request->input('academic_year_id');

        $coursesQuery = Course::where('dosen_id', $dosenId)
            ->with(['semester.academicYear', 'assignments']);
            
        // Filter courses by semester or academic year if provided
        if ($semesterId) {
            $coursesQuery->where('semester_id', $semesterId);
        }
        
        if ($academicYearId) {
            $coursesQuery->whereHas('semester', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        $courses = $coursesQuery->orderBy('created_at', 'desc')->get();
        
        // Eager load students with filtering
        $courses->load(['students' => function($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%");
            }
            $query->with(['submissions' => function($subQuery) {
                $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score');
            }]);
        }]);

        // Fetch options for filters
        $availableSemesters = \App\Models\Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $availableYears = \App\Models\AcademicYear::orderBy('year_start', 'desc')->get();

        return view('dosen.grades.index', compact('courses', 'availableSemesters', 'availableYears', 'search', 'semesterId', 'academicYearId'));
    }
}
