<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function create(Course $course)
    {
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        return view('dosen.exercises.create', compact('course'));
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
            'exercise_language' => 'required|in:html,css,javascript,htmlmixed',
            'starter_code' => 'required|string',
            'solution_code' => 'nullable|string',
            'required_keywords' => 'nullable|string',
            'hints' => 'nullable|string',
        ]);
        
        // Parse required keywords and hints from comma-separated strings
        $keywords = $request->required_keywords 
            ? array_map('trim', explode(',', $request->required_keywords))
            : [];
        
        $hints = $request->hints
            ? array_map('trim', explode("\n", $request->hints))
            : [];
        
        Assignment::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'type' => 'exercise',
            'auto_grade' => true,
            'exercise_config' => [
                'language' => $request->exercise_language,
                'starter_code' => $request->starter_code,
                'solution_code' => $request->solution_code,
                'required_keywords' => $keywords,
                'hints' => $hints,
            ],
        ]);
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Code Exercise berhasil ditambahkan!');
    }

    public function edit(Assignment $assignment)
    {
        $course = $assignment->course;
        
        // Check if dosen owns this course
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if this is an exercise
        if ($assignment->type !== 'exercise') {
            abort(404);
        }
        
        return view('dosen.exercises.edit', compact('assignment', 'course'));
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
            'exercise_language' => 'required|in:html,css,javascript,htmlmixed',
            'starter_code' => 'required|string',
            'solution_code' => 'nullable|string',
            'required_keywords' => 'nullable|string',
            'hints' => 'nullable|string',
        ]);
        
        $keywords = $request->required_keywords 
            ? array_map('trim', explode(',', $request->required_keywords))
            : [];
        
        $hints = $request->hints
            ? array_map('trim', explode("\n", $request->hints))
            : [];
        
        $assignment->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'exercise_config' => [
                'language' => $request->exercise_language,
                'starter_code' => $request->starter_code,
                'solution_code' => $request->solution_code,
                'required_keywords' => $keywords,
                'hints' => $hints,
            ],
        ]);
        
        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', 'Code Exercise berhasil diperbarui!');
    }
}
