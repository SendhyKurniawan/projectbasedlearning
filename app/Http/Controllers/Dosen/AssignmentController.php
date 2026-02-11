<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $assignments = $course->assignments()
            ->withCount('submissions')
            ->orderBy('deadline', 'desc')
            ->get();
        
        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
        ]);
        
        Assignment::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
        ]);
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function edit(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.assignments.edit', compact('assignment', 'course'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
        ]);
        
        $assignment->update($request->only(['title', 'description', 'deadline', 'max_score']));
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $assignment->delete();
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Tugas berhasil dihapus!');
    }

    public function submissions(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $submissions = $assignment->submissions()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        return view('dosen.assignments.submissions', compact('assignment', 'course', 'submissions'));
    }

    public function grade(Request $request, Submission $submission)
    {
        $assignment = $submission->assignment;
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        $request->validate([
            'score' => 'required|integer|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string',
        ]);
        
        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
        ]);
        
        return redirect()->back()
            ->with('success', 'Nilai berhasil diberikan!');
    }
}
