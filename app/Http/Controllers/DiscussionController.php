<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// Controller forum diskusi: daftar/cari/urut topik, plus CRUD topik. Daftar komentar dirender
// oleh komponen Livewire Discussion\Show pada halaman show.
class DiscussionController extends Controller
{
    // Daftar diskusi (cari/filter topik/urut terbaru atau terpopuler) + data sidebar yang di-cache.
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

        $sort = $request->get('sort', 'latest');
        if ($sort === 'popular') {
            $query->reorder()->orderByDesc('comments_count');
        }

        $discussions = $query->paginate(10)->withQueryString();

        // Cache 10 menit untuk data sidebar (topik populer, total, kontributor teratas).
        // Cache ini di-flush otomatis oleh hook Discussion::booted().
        $sidebar = Cache::remember('discussions:index:sidebar', 600, function () {
            return [
                'allTopics' => Discussion::selectRaw('topic, COUNT(*) as count')
                    ->whereNotNull('topic')
                    ->where('topic', '!=', '')
                    ->groupBy('topic')
                    ->orderByDesc('count')
                    ->limit(12)
                    ->get(),
                'totalCount' => Discussion::count(),
                'topContributors' => User::select('users.*')
                    ->selectRaw('COUNT(discussions.id) as discussions_count')
                    ->join('discussions', 'users.id', '=', 'discussions.user_id')
                    ->groupBy('users.id')
                    ->orderByDesc('discussions_count')
                    ->limit(5)
                    ->get(),
            ];
        });

        [$allTopics, $totalCount, $topContributors] = [
            $sidebar['allTopics'],
            $sidebar['totalCount'],
            $sidebar['topContributors'],
        ];

        return view('discussions.index', compact('discussions', 'allTopics', 'totalCount', 'topContributors'));
    }

    // Form buat topik diskusi (topik bisa dipraisi lewat query string).
    public function create(Request $request)
    {
        $topic = $request->query('topic');
        return view('discussions.create', compact('topic'));
    }

    // Simpan topik diskusi baru atas nama user yang login.
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

    // Tampilkan detail topik diskusi (komentar dikelola komponen Livewire pada view).
    public function show(Discussion $discussion)
    {
        $discussion->load(['user', 'comments.user']);
        return view('discussions.show', compact('discussion'));
    }

    // Form edit topik (hanya pemilik, via DiscussionPolicy).
    public function edit(Discussion $discussion)
    {
        $this->authorize('update', $discussion);
        return view('discussions.edit', compact('discussion'));
    }

    // Perbarui topik diskusi (hanya pemilik).
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

    // Hapus topik diskusi (hanya pemilik).
    public function destroy(Discussion $discussion)
    {
        $this->authorize('delete', $discussion);
        $discussion->delete();
        return redirect()->route('discussions.index')->with('success', 'Diskusi berhasil dihapus.');
    }
}
