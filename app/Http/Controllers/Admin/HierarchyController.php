<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;

class HierarchyController extends Controller
{
    /**
     * Redirect to the departments index.
     */
    public function index()
    {
        return redirect()->route('admin.hierarchy.departments.index');
    }

    /**
     * Step 1: Show Departments (Jurusan)
     */
    public function departments()
    {
        $departments = Department::select('id', 'name', 'code')->withCount('studyPrograms')->get();
        return view('admin.hierarchy.departments.index', compact('departments'));
    }

    /**
     * Step 2: Show Study Programs (Prodi) in a Department
     */
    public function studyPrograms(Department $department)
    {
        $studyPrograms = $department->studyPrograms()->select('id', 'department_id', 'name', 'code', 'level')->withCount('studentClasses')->get();
        return view('admin.hierarchy.departments.show', compact('department', 'studyPrograms'));
    }

    /**
     * Step 3: Show Semesters in a Study Program
     */
    public function semesters(StudyProgram $studyProgram)
    {
        // Get semesters that have classes in this study program, or just show all active semesters.
        // For flexibility, let's show all semesters, ordered by year and name.
        $semesters = Semester::with('academicYear:id,year_start,year_end')->select('id', 'academic_year_id', 'name', 'is_active')->orderBy('academic_year_id', 'desc')->get();
        return view('admin.hierarchy.study_programs.show', compact('studyProgram', 'semesters'));
    }

    /**
     * Step 4: Show Classes (and Semester Courses) in a Semester for a specific Study Program
     */
    public function classes(StudyProgram $studyProgram, Semester $semester)
    {
        // Classes in this Study Program and Semester
        $classes = StudentClass::where('study_program_id', $studyProgram->id)
            ->where('semester_id', $semester->id)
            ->select('id', 'study_program_id', 'semester_id', 'name')
            ->withCount('students')
            ->get();

        // Semester-level Courses (inherited by all classes in this semester)
        // Note: Currently, global vs specific is determined by student_class_id being null
        $semesterCourses = Course::where('semester_id', $semester->id)
            ->whereNull('student_class_id')
            ->select('id', 'semester_id', 'student_class_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'sks')
            ->with('dosen:id,name')
            ->get();

        // Used for adding a new course
        $dosens = User::where('role', 'dosen')->select('id', 'name')->get();

        return view('admin.hierarchy.semesters.show', compact('studyProgram', 'semester', 'classes', 'semesterCourses', 'dosens'));
    }

    /**
     * Step 5: Show Class Details (Students and Class Courses)
     */
    public function classDetails(StudentClass $studentClass)
    {
        $studentClass->load('studyProgram.department', 'semester.academicYear');

        $students = User::where('student_class_id', $studentClass->id)->select('id', 'name', 'email', 'nim')->get();

        // Semester-level Courses (Inherited)
        $inheritedCourses = Course::where('semester_id', $studentClass->semester_id)
            ->whereNull('student_class_id')
            ->select('id', 'semester_id', 'student_class_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'sks')
            ->with('dosen:id,name')
            ->get();

        // Class-specific Courses
        $classCourses = Course::where('student_class_id', $studentClass->id)
            ->select('id', 'semester_id', 'student_class_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'sks')
            ->with('dosen:id,name')
            ->get();

        // Used for adding a new course
        $dosens = User::where('role', 'dosen')->select('id', 'name')->get();

        return view('admin.hierarchy.student_classes.show', compact('studentClass', 'students', 'inheritedCourses', 'classCourses', 'dosens'));
    }

    /**
     * Helper: Add Course to Semester (Inherited)
     */
    public function addSemesterCourse(Request $request, StudyProgram $studyProgram, Semester $semester)
    {
        $validated = $request->validate([
            'kode_matkul' => 'required|string|unique:courses',
            'nama_matkul' => 'required|string|max:255',
            'sks' => 'required|integer|min:1',
            'dosen_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $course = new Course($validated);
        $course->semester_id = $semester->id;
        $course->student_class_id = null; // Important: Null means inherited
        $course->save();

        return redirect()->route('admin.hierarchy.study-programs.semesters.show', [$studyProgram, $semester])
                         ->with('success', 'Mata Kuliah berhasil ditambahkan ke Semester ini.');
    }

    /**
     * Helper: Add Course to Class (Specific)
     */
    public function addClassCourse(Request $request, StudentClass $studentClass)
    {
        $validated = $request->validate([
            'kode_matkul' => 'required|string|unique:courses',
            'nama_matkul' => 'required|string|max:255',
            'sks' => 'required|integer|min:1',
            'dosen_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $course = new Course($validated);
        $course->semester_id = $studentClass->semester_id;
        $course->student_class_id = $studentClass->id; // Important: Specific to class
        $course->save();

        return redirect()->route('admin.hierarchy.student-classes.show', $studentClass)
                         ->with('success', 'Mata Kuliah berhasil ditambahkan khusus untuk kelas ini.');
    }

    /**
     * Helper: Remove Course (Works for both)
     */
    public function removeCourse(Course $course)
    {
        $course->delete();
        return back()->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}
