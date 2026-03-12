<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\StudyProgram;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    public function index()
    {
        $studentClasses = StudentClass::with(['studyProgram', 'semester.academicYear'])->latest()->get();
        return view('admin.student_classes.index', compact('studentClasses'));
    }

    public function create()
    {
        $studyPrograms = StudyProgram::with('department')->get();
        $semesters = Semester::with('academicYear')->get();
        return view('admin.student_classes.create', compact('studyPrograms', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required|string|max:255',
        ]);

        StudentClass::create($validated);

        return redirect()->route('admin.student-classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(StudentClass $studentClass)
    {
        $studyPrograms = StudyProgram::with('department')->get();
        $semesters = Semester::with('academicYear')->get();
        return view('admin.student_classes.edit', compact('studentClass', 'studyPrograms', 'semesters'));
    }

    public function update(Request $request, StudentClass $studentClass)
    {
        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required|string|max:255',
        ]);

        $studentClass->update($validated);

        return redirect()->route('admin.student-classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(StudentClass $studentClass)
    {
        $studentClass->delete();
        return redirect()->route('admin.student-classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
