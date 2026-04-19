<?php

namespace App\Http\Controllers\Mahasiswa;

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
        $mahasiswaId = Auth::id();

        $search = $request->input('search');
        $semesterId = $request->input('semester_id');
        $academicYearId = $request->input('academic_year_id');

        $coursesQuery = Course::whereHas('students', function ($query) use ($mahasiswaId) {
            $query->where('mahasiswa_id', $mahasiswaId);
        })->with(['semester.academicYear', 'dosen:id,name', 'assignments.submissions' => function ($query) use ($mahasiswaId) {
            $query->where('mahasiswa_id', $mahasiswaId)->select('id', 'assignment_id', 'mahasiswa_id', 'score', 'status');
        }]);

        // Filter by semester
        if ($semesterId) {
            $coursesQuery->where('semester_id', $semesterId);
        }

        // Filter by academic year
        if ($academicYearId) {
            $coursesQuery->whereHas('semester', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        // Search by course name or code
        if ($search) {
            $coursesQuery->where(function ($q) use ($search) {
                $q->where('nama_matkul', 'like', "%{$search}%")
                  ->orWhere('kode_matkul', 'like', "%{$search}%");
            });
        }

        $courses = $coursesQuery->orderBy('created_at', 'desc')->get();

        // Fetch filter options (only semesters/years relevant to enrolled courses)
        $availableSemesters = Semester::with('academicYear:id,year_start,year_end')
            ->orderBy('start_date', 'desc')->get();
        $availableYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return view('mahasiswa.grades.index', compact(
            'courses',
            'availableSemesters',
            'availableYears',
            'search',
            'semesterId',
            'academicYearId'
        ));
    }
}
