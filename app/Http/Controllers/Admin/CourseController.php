<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Course::query()->with('dosen')->withCount('students');

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

        $courses = $query->latest()->paginate(10);

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dosens = \App\Models\User::where('role', 'dosen')->get();
        return view('admin.courses.create', compact('dosens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_matkul' => ['required', 'string', 'max:255'],
            'kode_matkul' => ['required', 'string', 'max:50', 'unique:courses,kode_matkul'],
            'description' => ['nullable', 'string'],
            'dosen_id' => ['required', 'exists:users,id'],
        ]);

        // Verify assigned user is actually a dosen
        $dosen = \App\Models\User::find($request->dosen_id);
        if ($dosen->role !== 'dosen') {
            return back()->withErrors(['dosen_id' => 'Selected user is not a lecturer.']);
        }

        \App\Models\Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $course = \App\Models\Course::with('students')->findOrFail($id);
        $dosens = \App\Models\User::where('role', 'dosen')->get();
        
        // Get students not yet enrolled in this course for the dropdown
        $enrolledStudentIds = $course->students->pluck('id')->toArray();
        $availableStudents = \App\Models\User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('name')
            ->get();

        return view('admin.courses.edit', compact('course', 'dosens', 'availableStudents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $course = \App\Models\Course::findOrFail($id);

        $validated = $request->validate([
            'nama_matkul' => ['required', 'string', 'max:255'],
            'kode_matkul' => ['required', 'string', 'max:50', 'unique:courses,kode_matkul,'.$course->id],
            'description' => ['nullable', 'string'],
            'dosen_id' => ['required', 'exists:users,id'],
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $course = \App\Models\Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    /**
     * Enroll a student to the course.
     */
    public function enroll(Request $request, string $courseId)
    {
        $course = \App\Models\Course::findOrFail($courseId);
        
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id']
        ]);

        $course->students()->attach($validated['student_id'], ['enrolled_at' => now()]);

        return back()->with('success', 'Student enrolled successfully.');
    }

    /**
     * Unenroll a student from the course.
     */
    public function unenroll(string $courseId, string $studentId)
    {
        $course = \App\Models\Course::findOrFail($courseId);
        $course->students()->detach($studentId);

        return back()->with('success', 'Student removed from course successfully.');
    }
}
