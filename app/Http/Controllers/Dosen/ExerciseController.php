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
        $this->authorize('update', $course);

        return view('dosen.exercises.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
            'exercise_language' => 'required|in:' . implode(',', config('code_execution.all_languages')),
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

        $autoGrade = in_array(
            $request->exercise_language,
            config('code_execution.server_side_languages'),
            true
        );

        Assignment::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'type' => 'exercise',
            'auto_grade' => $autoGrade,
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
        $this->authorize('update', $assignment);

        if ($assignment->type !== 'exercise') {
            abort(404);
        }

        $course = $assignment->course;

        return view('dosen.exercises.edit', compact('assignment', 'course'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if ($assignment->type !== 'exercise') {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
            'exercise_language' => 'required|in:' . implode(',', config('code_execution.all_languages')),
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

        $autoGrade = in_array(
            $request->exercise_language,
            config('code_execution.server_side_languages'),
            true
        );

        $assignment->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'auto_grade' => $autoGrade,
            'exercise_config' => [
                'language' => $request->exercise_language,
                'starter_code' => $request->starter_code,
                'solution_code' => $request->solution_code,
                'required_keywords' => $keywords,
                'hints' => $hints,
            ],
        ]);

        return redirect()->route('dosen.assignments.index', $assignment->course)
            ->with('success', 'Code Exercise berhasil diperbarui!');
    }
}
