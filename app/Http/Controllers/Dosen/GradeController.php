<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Semester;
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

        if ($semesterId) {
            $coursesQuery->where('semester_id', $semesterId);
        }

        if ($academicYearId) {
            $coursesQuery->whereHas('semester', fn($q) => $q->where('academic_year_id', $academicYearId));
        }

        $courses = $coursesQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $assignmentIds = $courses->getCollection()
            ->flatMap(fn($c) => $c->assignments->pluck('id'))
            ->unique()
            ->all();

        $courses->getCollection()->load([
            'students' => function ($query) use ($search) {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('nim', 'like', "%{$search}%");
                    });
                }
            },
            'students.submissions' => function ($subQuery) use ($assignmentIds) {
                $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score')
                    ->whereIn('assignment_id', $assignmentIds);
            },
        ]);

        $availableSemesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $availableYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return view('dosen.grades.index', compact(
            'courses', 'availableSemesters', 'availableYears', 'search', 'semesterId', 'academicYearId'
        ));
    }
}
