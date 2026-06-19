<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Http\Request;

// Controller CRUD program studi (resource route lama; halaman terpadu ada di AkademikController).
class StudyProgramController extends Controller
{
    // Tampilkan daftar prodi beserta jurusan & jumlah kelasnya.
    public function index()
    {
        $studyPrograms = StudyProgram::with('department')->withCount('studentClasses')->latest()->get();
        return view('admin.study_programs.index', compact('studyPrograms'));
    }

    // Tampilkan form tambah prodi (perlu daftar jurusan).
    public function create()
    {
        $departments = Department::all();
        return view('admin.study_programs.create', compact('departments'));
    }

    // Simpan prodi baru (kode unik, level D3/D4/S1/S2/S3).
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

    // Tampilkan form edit prodi.
    public function edit(StudyProgram $studyProgram)
    {
        $departments = Department::all();
        return view('admin.study_programs.edit', compact('studyProgram', 'departments'));
    }

    // Perbarui data prodi (kode tetap unik kecuali milik dirinya sendiri).
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

    // Hapus prodi.
    public function destroy(StudyProgram $studyProgram)
    {
        $studyProgram->delete();
        return redirect()->route('admin.study-programs.index')->with('success', 'Program Studi berhasil dihapus.');
    }
}
