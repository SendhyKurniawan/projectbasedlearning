<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Course;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ideally discussions are filtered by course
        $courseId = $request->query('course_id');
        $query = Discussion::with('user')->withCount('comments')->latest();
        
        if ($courseId) {
            $query->where('course_id', $courseId);
            $course = Course::find($courseId);
        } else {
            $course = null;
        }

        $discussions = $query->paginate(10);
        
        return view('discussions.index', compact('discussions', 'course'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;
        $courses = $course ? collect([$course]) : auth()->user()->courses; // Or enrolled courses

        return view('discussions.create', compact('courses', 'course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();

        Discussion::create($validated);

        return redirect()->route('discussions.index', ['course_id' => $validated['course_id']])->with('success', 'Diskusi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discussion $discussion)
    {
        $discussion->load(['user', 'comments.user']);
        return view('discussions.show', compact('discussion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discussion $discussion)
    {
        $this->authorize('update', $discussion);
        return view('discussions.edit', compact('discussion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discussion $discussion)
    {
        $this->authorize('update', $discussion);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $discussion->update($validated);

        return redirect()->route('discussions.show', $discussion)->with('success', 'Diskusi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discussion $discussion)
    {
        $this->authorize('delete', $discussion);
        $courseId = $discussion->course_id;
        $discussion->delete();
        return redirect()->route('discussions.index', ['course_id' => $courseId])->with('success', 'Diskusi berhasil dihapus.');
    }
}
