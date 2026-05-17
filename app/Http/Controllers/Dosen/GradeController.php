<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = Auth::id();

        $search          = $request->input('search');
        $semesterId      = $request->input('semester_id');
        $academicYearId  = $request->input('academic_year_id');
        $departmentId    = $request->input('department_id');
        $studyProgramId  = $request->input('study_program_id');
        $studentClassId  = $request->input('student_class_id');

        $coursesQuery = Course::where('dosen_id', $dosenId)
            ->with(['semester.academicYear', 'studentClass.studyProgram.department', 'assignments']);

        if ($semesterId) {
            $coursesQuery->where('semester_id', $semesterId);
        }

        if ($academicYearId) {
            $coursesQuery->whereHas('semester', fn ($q) => $q->where('academic_year_id', $academicYearId));
        }

        if ($studentClassId) {
            $coursesQuery->where('student_class_id', $studentClassId);
        }

        if ($studyProgramId) {
            $coursesQuery->whereHas('studentClass', fn ($q) => $q->where('study_program_id', $studyProgramId));
        }

        if ($departmentId) {
            $coursesQuery->whereHas('studentClass.studyProgram', fn ($q) => $q->where('department_id', $departmentId));
        }

        $courses = $coursesQuery->orderBy('created_at', 'desc')->get();

        // Eager load students (with optional search) and their submissions
        $courses->load(['students' => function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%");
            }
            $query->with(['submissions' => function ($subQuery) {
                $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score', 'status');
            }]);
        }]);

        // Group sibling courses by course_group_key (same pattern as DashboardController)
        $courseGroups = $courses->groupBy('course_group_key')->map(fn ($g) => [
            'nama_matkul'       => $g->first()->nama_matkul,
            'kode_matkul'       => $g->first()->kode_matkul,
            'semester'          => $g->first()->semester,
            'courses'           => $g->sortBy(fn ($c) => $c->studentClass?->name ?? '')->values(),
            'total_students'    => $g->sum(fn ($c) => $c->students->count()),
            'total_assignments' => $g->sum(fn ($c) => $c->assignments->count()),
        ])->values();

        // Filter dropdown options
        $availableYears        = AcademicYear::orderBy('year_start', 'desc')->get();
        $availableSemesters    = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $availableDepartments  = Department::orderBy('name')->get();
        $availablePrograms     = StudyProgram::with('department')->orderBy('name')->get();
        $availableClasses      = StudentClass::with('studyProgram')->orderBy('name')->get();

        return view('dosen.grades.index', compact(
            'courseGroups',
            'search',
            'semesterId',
            'academicYearId',
            'departmentId',
            'studyProgramId',
            'studentClassId',
            'availableYears',
            'availableSemesters',
            'availableDepartments',
            'availablePrograms',
            'availableClasses',
        ));
    }

    /**
     * Export per-kelas grade recap as CSV.
     */
    public function export(Course $course)
    {
        $this->authorize('view', $course);

        $course->load('studentClass', 'assignments', 'students.submissions');

        $filename = 'nilai-'
            . str_replace('/', '-', $course->kode_matkul)
            . '-'
            . ($course->studentClass?->name ?? 'kelas')
            . '.csv';

        return response()->streamDownload(function () use ($course) {
            $out = fopen('php://output', 'w');

            // Header row
            fputcsv($out, array_merge(
                ['NIM', 'Nama'],
                $course->assignments->pluck('title')->all(),
                ['Rata-rata']
            ));

            foreach ($course->students as $s) {
                $row = [$s->nim, $s->name];
                $sum = 0;
                $n   = 0;

                foreach ($course->assignments as $a) {
                    $score = $s->submissions->where('assignment_id', $a->id)->first()?->score;
                    $row[] = $score ?? '';
                    if ($score !== null) {
                        $sum += $score;
                        $n++;
                    }
                }

                $row[] = $n > 0 ? number_format($sum / $n, 2) : '';
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * PATCH endpoint for inline single-cell grade editing.
     */
    public function quickGrade(Request $request, Assignment $assignment, User $mahasiswa)
    {
        $this->authorize('update', $assignment);

        $data = $request->validate([
            'score'    => 'required|integer|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'mahasiswa_id'  => $mahasiswa->id,
                'group_id'      => null,
            ],
            [
                'score'    => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'status'   => 'graded',
            ]
        );

        // Set submitted_at only if it was previously null (don't overwrite an existing timestamp)
        if (! $submission->submitted_at) {
            $submission->update(['submitted_at' => now()]);
        }

        return response()->json([
            'ok'     => true,
            'score'  => $submission->score,
            'status' => $submission->status,
        ]);
    }
}
