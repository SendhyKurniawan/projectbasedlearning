<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $semesterId = $request->input('semester_id');
        $academicYearId = $request->input('academic_year_id');
        $dosenId = $request->input('dosen_id');

        $coursesQuery = Course::query()
            ->with([
                'semester:id,name,academic_year_id',
                'semester.academicYear:id,year_start,year_end',
                'dosen:id,name',
                'assignments:id,course_id,title,max_score',
            ])
            ->select('id', 'semester_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'created_at');

        // Filter by semester
        if ($semesterId) {
            $coursesQuery->where('semester_id', $semesterId);
        }

        // Filter by academic year (via semester relation)
        if ($academicYearId) {
            $coursesQuery->whereHas('semester', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        // Filter by dosen
        if ($dosenId) {
            $coursesQuery->where('dosen_id', $dosenId);
        }

        $courses = $coursesQuery->orderBy('created_at', 'desc')->get();

        // Eager load students with optional search filter
        $courses->load(['students' => function ($query) use ($search) {
            $query->select('users.id', 'users.name', 'users.nim');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%");
                });
            }
            $query->with(['submissions' => function ($subQuery) {
                $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score');
            }]);
        }]);

        // Fetch filter options
        $availableSemesters = Semester::with('academicYear:id,year_start,year_end')
            ->orderBy('start_date', 'desc')->get();
        $availableYears = AcademicYear::orderBy('year_start', 'desc')->get();
        $availableDosens = User::where('role', 'dosen')
            ->orderBy('name')->select('id', 'name')->get();

        return view('admin.grades.index', compact(
            'courses',
            'availableSemesters',
            'availableYears',
            'availableDosens',
            'search',
            'semesterId',
            'academicYearId',
            'dosenId'
        ));
    }
}
