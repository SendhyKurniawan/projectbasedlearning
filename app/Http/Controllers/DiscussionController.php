<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Discussion::with('user')->withCount('comments')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        if ($sort === 'popular') {
            $query->reorder()->orderByDesc('comments_count');
        }

        $discussions = $query->paginate(10)->withQueryString();

        // Get all unique topics with counts for the sidebar
        $allTopics = Discussion::selectRaw('topic, COUNT(*) as count')
            ->whereNotNull('topic')
            ->where('topic', '!=', '')
            ->groupBy('topic')
            ->orderByDesc('count')
            ->limit(12)
            ->get();

        // Total discussion count
        $totalCount = Discussion::count();

        // Top contributors (users with most discussions)
        $topContributors = User::select('users.*')
            ->selectRaw('COUNT(discussions.id) as discussions_count')
            ->join('discussions', 'users.id', '=', 'discussions.user_id')
            ->groupBy('users.id')
            ->orderByDesc('discussions_count')
            ->limit(5)
            ->get();

        return view('discussions.index', compact('discussions', 'allTopics', 'totalCount', 'topContributors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $topic = $request->query('topic');
        return view('discussions.create', compact('topic'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();

        Discussion::create($validated);

        return redirect()->route('discussions.index')->with('success', 'Diskusi berhasil dibuat.');
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
        $discussion->delete();
        return redirect()->route('discussions.index')->with('success', 'Diskusi berhasil dihapus.');
    }
}
