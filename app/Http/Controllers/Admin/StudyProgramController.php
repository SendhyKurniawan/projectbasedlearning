<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Http\Request;

class StudyProgramController extends Controller
{
    public function index()
    {
        $studyPrograms = StudyProgram::with('department')->withCount('studentClasses')->latest()->get();
        return view('admin.study_programs.index', compact('studyPrograms'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.study_programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:study_programs,code',
            'level' => 'required|in:D3,D4,S1,S2,S3',
        ]);

        StudyProgram::create($validated);

        return redirect()->route('admin.study-programs.index')->with('success', 'Program Studi berhasil ditambahkan.');
    }

    public function edit(StudyProgram $studyProgram)
    {
        $departments = Department::all();
        return view('admin.study_programs.edit', compact('studyProgram', 'departments'));
    }

    public function update(Request $request, StudyProgram $studyProgram)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:study_programs,code,' . $studyProgram->id,
            'level' => 'required|in:D3,D4,S1,S2,S3',
        ]);

        $studyProgram->update($validated);

        return redirect()->route('admin.study-programs.index')->with('success', 'Program Studi berhasil diperbarui.');
    }

    public function destroy(StudyProgram $studyProgram)
    {
        $studyProgram->delete();
        return redirect()->route('admin.study-programs.index')->with('success', 'Program Studi berhasil dihapus.');
    }
}
