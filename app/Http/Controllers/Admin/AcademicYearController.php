<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::with('semesters')->latest()->get();
        return view('admin.academic_years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.academic_years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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
    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Akademik berhasil dihapus.');
    }
}
