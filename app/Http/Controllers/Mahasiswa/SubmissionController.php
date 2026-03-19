<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class SubmissionController extends Controller
{
    public function create(Request $request)
    {
        $assignment_id = $request->query('assignment_id');
        $assignment = Assignment::with('course')->findOrFail($assignment_id);
        
        // Check if student is enrolled in the course
        $mahasiswa = auth()->user();
        if (!$mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
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
        $assignment = Assignment::findOrFail($request->assignment_id);
        
        $rules = [
            'assignment_id' => 'required|exists:assignments,id',
            'notes' => 'nullable|string',
        ];

        if ($assignment->submission_format === 'url') {
            $rules['url_link'] = 'required|url|max:2048';
        } else {
            $rules['file'] = 'required|file|max:10240'; // Max 10MB
        }

        $request->validate($rules);
        
        $mahasiswa = auth()->user();

        // Check if student is enrolled
        if (!$mahasiswa->enrollments()->where('courses.id', $assignment->course_id)->exists()) {
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

        // Handle file or url upload
        $file_path = null;
        $url_link = null;

        if ($assignment->submission_format === 'url') {
            $url_link = $request->url_link;
        } else {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $mahasiswa->id . '_' . $file->getClientOriginalName();
                $file_path = $file->storeAs('submissions', $filename, 'public');
            }
        }
        
        // Create submission
        Submission::create([
            'assignment_id' => $assignment->id,
            'mahasiswa_id' => $mahasiswa->id,
            'file_path' => $file_path,
            'url_link' => $url_link,
            'notes' => $request->notes,
            'submitted_at' => now(),
        ]);
        
        // Notify Dosen
        $dosen = User::find($assignment->course->dosen_id);
        if ($dosen) {
            Notification::send($dosen, new SubmissionNotification(
                $mahasiswa->name,
                $assignment->title,
                route('dosen.assignments.submissions', $assignment)
            ));
        }
        
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
        
        $rules = [
            'notes' => 'nullable|string',
        ];

        if ($submission->assignment->submission_format === 'url') {
            $rules['url_link'] = 'nullable|url|max:2048';
        } else {
            $rules['file'] = 'nullable|file|max:10240';
        }
        
        $request->validate($rules);
        
        $data = ['notes' => $request->notes];
        
        // Handle new file or url upload depending on format
        if ($submission->assignment->submission_format === 'url') {
            if ($request->filled('url_link')) {
                $data['url_link'] = $request->url_link;
                // Don't delete old file if switching formats here, though it relies on Assignment editing
            }
        } else {
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($submission->file_path) {
                    Storage::disk('public')->delete($submission->file_path);
                }
                
                $file = $request->file('file');
                $filename = time() . '_' . auth()->id() . '_' . $file->getClientOriginalName();
                $data['file_path'] = $file->storeAs('submissions', $filename, 'public');
            }
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
