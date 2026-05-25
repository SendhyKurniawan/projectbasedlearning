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
        $siblings = $course->siblings();
        return view('dosen.exercises.create', compact('course', 'siblings'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:100',
            'exercise_language' => 'required|in:html,css,javascript,htmlmixed,java,php,csharp',
            'starter_code' => 'required|string',
            'solution_code' => 'nullable|string',
            'required_keywords' => 'nullable|string',
            'hints' => 'nullable|string',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids ?? [])
            ->map(fn($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        $keywords = $request->required_keywords
            ? array_map('trim', explode(',', $request->required_keywords))
            : [];
        $hints = $request->hints
            ? array_map('trim', explode("\n", $request->hints))
            : [];

        $sharedData = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'max_score' => $request->max_score,
            'type' => 'exercise',
            'exercise_config' => [
                'language' => $request->exercise_language,
                'starter_code' => $request->starter_code,
                'solution_code' => $request->solution_code,
                'required_keywords' => $keywords,
                'hints' => $hints,
            ],
        ];

        Assignment::create(array_merge($sharedData, ['course_id' => $course->id]));

        $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
        foreach ($targetCourses as $sibling) {
            Assignment::create(array_merge($sharedData, ['course_id' => $sibling->id]));
        }

        $msg = 'Code Exercise berhasil ditambahkan!';
        if ($targetIds->count()) {
            $msg .= " Disalin ke {$targetIds->count()} kelas lain.";
        }

        return redirect()->route('dosen.assignments.index', $course)
            ->with('success', $msg);
    }

    public function edit(Assignment $assignment)
    {
        $course = $assignment->course;
        $this->authorize('update', $course);
        
        // Check if this is an exercise
        if ($assignment->type !== 'exercise') {
            abort(404);
        }
        
        return view('dosen.exercises.edit', compact('assignment', 'course'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $course = $assignment->course;
        $this->authorize('update', $course);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:1|max:100',
            'exercise_language' => 'required|in:html,css,javascript,htmlmixed,java,php,csharp',
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
