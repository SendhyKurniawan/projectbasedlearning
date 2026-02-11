<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function create(Request $request)
    {
        $assignment_id = $request->query('assignment_id');
        $assignment = Assignment::with('course')->findOrFail($assignment_id);
        
        // Check if student is enrolled in the course
        $mahasiswa = auth()->user();
        if (!$mahasiswa->enrolledCourses()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }
        
        // Check if already submitted
        $existing = Submission::where('assignment_id', $assignment_id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();
        
        return view('mahasiswa.submissions.create', compact('assignment', 'existing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'notes' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // Max 10MB
        ]);
        
        $mahasiswa = auth()->user();
        $assignment = Assignment::findOrFail($request->assignment_id);
        
        // Check if student is enrolled
        if (!$mahasiswa->enrolledCourses()->where('courses.id', $assignment->course_id)->exists()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }
        
        // Check if already submitted
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }
        
        // Handle file upload
        $file_path = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $mahasiswa->id . '_' . $file->getClientOriginalName();
            $file_path = $file->storeAs('submissions', $filename, 'public');
        }
        
        // Create submission
        Submission::create([
            'assignment_id' => $assignment->id,
            'mahasiswa_id' => $mahasiswa->id,
            'file_path' => $file_path,
            'notes' => $request->notes,
            'submitted_at' => now(),
        ]);
        
        return redirect()->route('mahasiswa.courses.show', $assignment->course_id)
            ->with('success', 'Tugas berhasil dikumpulkan!');
    }

    public function edit(Submission $submission)
    {
        // Check ownership
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if already graded
        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat diubah.');
        }
        
        $assignment = $submission->assignment()->with('course')->first();
        
        return view('mahasiswa.submissions.edit', compact('submission', 'assignment'));
    }

    public function update(Request $request, Submission $submission)
    {
        // Check ownership
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if already graded
        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat diubah.');
        }
        
        $request->validate([
            'notes' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);
        
        $data = ['notes' => $request->notes];
        
        // Handle new file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . auth()->id() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('submissions', $filename, 'public');
        }
        
        $submission->update($data);
        
        return redirect()->route('mahasiswa.courses.show', $submission->assignment->course_id)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(Submission $submission)
    {
        // Check ownership
        if ($submission->mahasiswa_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if already graded
        if ($submission->score !== null) {
            return redirect()->back()->with('error', 'Tugas yang sudah dinilai tidak dapat dihapus.');
        }
        
        // Delete file if exists
        if ($submission->file_path) {
            Storage::disk('public')->delete($submission->file_path);
        }
        
        $course_id = $submission->assignment->course_id;
        $submission->delete();
        
        return redirect()->route('mahasiswa.courses.show', $course_id)
            ->with('success', 'Tugas berhasil dihapus!');
    }
}
