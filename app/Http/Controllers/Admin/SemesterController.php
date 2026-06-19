<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

// Controller CRUD semester (resource route lama; halaman terpadu ada di AkademikController).
class SemesterController extends Controller
{
    // Tampilkan daftar semester beserta tahun ajarannya.
    public function index()
    {
        $semesters = Semester::with('academicYear')->latest()->get();
        return view('admin.semesters.index', compact('semesters'));
    }

    // Form tambah semester (perlu daftar tahun ajaran).
    public function create()
    {
        $academicYears = AcademicYear::all();
        return view('admin.semesters.create', compact('academicYears'));
    }

    // Simpan semester baru (Ganjil/Genap); bila ditandai aktif, nonaktifkan semester lain.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|in:Ganjil,Genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        Semester::create($validated);

        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil ditambahkan.');
    }

    // Tidak dipakai (placeholder route resource).
    public function show(Semester $semester)
    {
        //
    }

    // Form edit semester.
    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::all();
        return view('admin.semesters.edit', compact('semester', 'academicYears'));
    }

    // Perbarui semester; jaga agar hanya satu semester yang aktif.
    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|in:Ganjil,Genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            Semester::where('is_active', true)->where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $semester->update($validated);

        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil diperbarui.');
    }

    // Hapus semester.
    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil dihapus.');
    }
}
