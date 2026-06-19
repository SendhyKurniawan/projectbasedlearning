<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

// Controller CRUD tahun ajaran (resource route lama; halaman terpadu di AkademikController).
class AcademicYearController extends Controller
{
    // Tampilkan daftar tahun ajaran beserta jumlah semesternya.
    public function index()
    {
        $academicYears = AcademicYear::withCount('semesters')->latest()->get();
        return view('admin.academic_years.index', compact('academicYears'));
    }

    // Tampilkan form tambah tahun ajaran.
    public function create()
    {
        return view('admin.academic_years.create');
    }

    // Simpan tahun ajaran baru; bila ditandai aktif, nonaktifkan tahun ajaran lain dulu.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_start' => 'required|string|max:4',
            'year_end' => 'required|string|max:4',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($validated);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Akademik berhasil ditambahkan.');
    }

    // Tidak dipakai (placeholder route resource).
    public function show(string $id)
    {
        //
    }

    // Tampilkan form edit tahun ajaran.
    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    // Perbarui tahun ajaran; jaga agar hanya satu yang berstatus aktif.
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year_start' => 'required|string|max:4',
            'year_end' => 'required|string|max:4',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            AcademicYear::where('is_active', true)->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update($validated);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Akademik berhasil diperbarui.');
    }

    // Hapus tahun ajaran.
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Akademik berhasil dihapus.');
    }
}
