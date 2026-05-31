<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Course::query()->with('dosen:id,name', 'semester:id,name,academic_year_id', 'semester.academicYear:id,year_start,year_end', 'studentClass:id,name')->select('id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'sks', 'semester_id', 'student_class_id', 'created_at')->withCount('students');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_matkul', 'like', "%{$search}%")
                  ->orWhere('kode_matkul', 'like', "%{$search}%")
                  ->orWhereHas('dosen', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('dosen_id')) {
            $query->where('dosen_id', $request->dosen_id);
        }

        $courses = $query->latest()->paginate(10);

        $availableSemesters = \App\Models\Semester::with('academicYear:id,year_start,year_end')
            ->orderBy('start_date', 'desc')->get();
        $availableDosens = \App\Models\User::where('role', 'dosen')
            ->orderBy('name')->select('id', 'name')->get();

        return view('admin.courses.index', compact('courses', 'availableSemesters', 'availableDosens'));
    }

    public function create()
    {
        $dosens = \App\Models\User::where('role', 'dosen')->select('id', 'name')->get();
        $classes = \App\Models\StudentClass::with('studyProgram:id,name,level')->select('id', 'name', 'study_program_id')->get();
        $semesters = \App\Models\Semester::with('academicYear:id,year_start,year_end')->select('id', 'name', 'academic_year_id')->get();
        return view('admin.courses.create', compact('dosens', 'classes', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_matkul'      => ['required', 'string', 'max:255'],
            'kode_matkul'      => [
                'required', 'string', 'max:50',
                Rule::unique('courses')->where(fn ($q) => $q
                    ->where('dosen_id', $request->dosen_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('student_class_id', $request->student_class_id ?: null)
                ),
            ],
            'description'      => ['nullable', 'string'],
            'dosen_id'         => ['required', 'exists:users,id'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
        ]);

        $dosen = \App\Models\User::find($request->dosen_id);
        if ($dosen->role !== 'dosen') {
            return back()->withErrors(['dosen_id' => 'Selected user is not a lecturer.']);
        }

        \App\Models\Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $course = \App\Models\Course::with('students')->findOrFail($id);
        $dosens = \App\Models\User::where('role', 'dosen')->get();
        $classes = \App\Models\StudentClass::with('studyProgram')->get();
        $semesters = \App\Models\Semester::with('academicYear')->get();

        $enrolledStudentIds = $course->students->pluck('id')->toArray();
        $availableStudents = \App\Models\User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('name')
            ->get();

        return view('admin.courses.edit', compact('course', 'dosens', 'availableStudents', 'classes', 'semesters'));
    }

    public function update(Request $request, string $id)
    {
        $course = \App\Models\Course::findOrFail($id);

        $validated = $request->validate([
            'nama_matkul'      => ['required', 'string', 'max:255'],
            'kode_matkul'      => [
                'required', 'string', 'max:50',
                Rule::unique('courses')->where(fn ($q) => $q
                    ->where('dosen_id', $request->dosen_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('student_class_id', $request->student_class_id ?: null)
                )->ignore($course->id),
            ],
            'description'      => ['nullable', 'string'],
            'dosen_id'         => ['required', 'exists:users,id'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(string $id)
    {
        $course = \App\Models\Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function enroll(Request $request, string $courseId)
    {
        $course = \App\Models\Course::findOrFail($courseId);
        
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id']
        ]);

        $course->students()->attach($validated['student_id'], ['enrolled_at' => now()]);

        return back()->with('success', 'Student enrolled successfully.');
    }

    public function unenroll(string $courseId, string $studentId)
    {
        $course = \App\Models\Course::findOrFail($courseId);
        $course->students()->detach($studentId);

        return back()->with('success', 'Student removed from course successfully.');
    }
}
