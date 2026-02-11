<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semesters = Semester::with('academicYear')->latest()->get();
        return view('admin.semesters.index', compact('semesters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        return view('admin.semesters.create', compact('academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Semester $semester)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::all();
        return view('admin.semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil dihapus.');
    }
}
