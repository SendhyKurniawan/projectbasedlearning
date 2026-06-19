<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

// Controller CRUD jurusan (resource route lama; halaman terpadu ada di AkademikController).
class DepartmentController extends Controller
{
    // Tampilkan daftar jurusan beserta jumlah program studinya.
    public function index()
    {
        $departments = Department::withCount('studyPrograms')->latest()->get();
        return view('admin.departments.index', compact('departments'));
    }

    // Tampilkan form tambah jurusan.
    public function create()
    {
        return view('admin.departments.create');
    }

    // Simpan jurusan baru (kode harus unik).
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    // Tampilkan form edit jurusan.
    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    // Perbarui data jurusan (kode tetap unik, kecuali milik dirinya sendiri).
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    // Hapus jurusan.
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
