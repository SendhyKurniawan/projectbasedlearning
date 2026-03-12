<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Announcement::with('author')->latest();

        if ($user->role === 'mahasiswa') {
            // Mahasiswa sees 'all', 'mahasiswa', and 'specific' (if they are in the pivot)
            $query->where(function ($q) use ($user) {
                $q->whereIn('target_audience', ['all', 'mahasiswa'])
                  ->orWhereHas('targetedUsers', function ($subq) use ($user) {
                      $subq->where('user_id', $user->id);
                  });
            });
        } elseif ($user->role === 'dosen') {
             // Dosen sees 'all', 'dosen', announcements they authored, or targeted specifically to them
             $query->where(function ($q) use ($user) {
                $q->whereIn('target_audience', ['all', 'dosen'])
                  ->orWhere('user_id', $user->id) // Ones they authored
                  ->orWhereHas('targetedUsers', function ($subq) use ($user) {
                      $subq->where('user_id', $user->id);
                  });
            });
        }
        // Admin sees everything

        $announcements = $query->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Allowed targets based on role
        if ($user->role === 'admin') {
            $targets = ['all' => 'Semua Pengguna', 'dosen' => 'Seluruh Dosen', 'mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Pengguna Spesifik'];
            $users = User::where('id', '!=', $user->id)->select('id', 'name', 'role')->get();
        } else if ($user->role === 'dosen') {
            $targets = ['mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Mahasiswa Spesifik'];
            // Dosen only targets mahasiswa for simplicity, or could scope it to their classes
            $users = User::where('role', 'mahasiswa')->select('id', 'name', 'role')->get();
        } else {
            abort(403, 'Akses ditolak.');
        }

        return view('announcements.create', compact('targets', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,dosen,mahasiswa,specific',
        ];

        if ($request->target_audience === 'specific') {
            $rules['specific_users'] = 'required|array';
            $rules['specific_users.*'] = 'exists:users,id';
        }

        // Dosen restrictions validation
        if ($user->role === 'dosen') {
             if (!in_array($request->target_audience, ['mahasiswa', 'specific'])) {
                 abort(403, 'Akses audiens ditolak.');
             }
        }

        $validated = $request->validate($rules);

        $announcement = Announcement::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_audience' => $validated['target_audience'],
        ]);

        if ($validated['target_audience'] === 'specific') {
            $announcement->targetedUsers()->sync($validated['specific_users']);
        }

        // Notification Logic
        $notifiableUsers = collect();
        if ($validated['target_audience'] === 'all') {
            $notifiableUsers = User::where('id', '!=', $user->id)->get();
        } elseif ($validated['target_audience'] === 'mahasiswa') {
            $notifiableUsers = User::where('role', 'mahasiswa')->get();
        } elseif ($validated['target_audience'] === 'dosen') {
             $notifiableUsers = User::where('role', 'dosen')->where('id', '!=', $user->id)->get();
        } elseif ($validated['target_audience'] === 'specific') {
             $notifiableUsers = User::whereIn('id', $validated['specific_users'])->get();
        }

        if ($notifiableUsers->isNotEmpty()) {
            Notification::send($notifiableUsers, new AnnouncementNotification($announcement->id, $announcement->title, $user->name));
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $user = Auth::user();

        // Check if user is allowed to view
        $isAllowed = false;
        
        if ($user->role === 'admin' || $announcement->user_id === $user->id) {
            $isAllowed = true;
        } elseif ($announcement->target_audience === 'all') {
            $isAllowed = true;
        } elseif ($announcement->target_audience === 'mahasiswa' && $user->role === 'mahasiswa') {
             $isAllowed = true;
        } elseif ($announcement->target_audience === 'dosen' && $user->role === 'dosen') {
             $isAllowed = true;
        } elseif ($announcement->target_audience === 'specific') {
            if ($announcement->targetedUsers()->where('user_id', $user->id)->exists()) {
                $isAllowed = true;
            }
        }

        if (!$isAllowed) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengumuman ini.');
        }

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
         if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
             abort(403);
         }

         $user = Auth::user();
         if ($user->role === 'admin') {
            $targets = ['all' => 'Semua Pengguna', 'dosen' => 'Seluruh Dosen', 'mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Pengguna Spesifik'];
            $users = User::where('id', '!=', $user->id)->select('id', 'name', 'role')->get();
        } else {
            $targets = ['mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Mahasiswa Spesifik'];
            $users = User::where('role', 'mahasiswa')->select('id', 'name', 'role')->get();
        }

        $selectedUsers = $announcement->targetedUsers->pluck('id')->toArray();

        return view('announcements.edit', compact('announcement', 'targets', 'users', 'selectedUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
             abort(403);
        }

        $user = Auth::user();
        
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,dosen,mahasiswa,specific',
        ];

        if ($request->target_audience === 'specific') {
            $rules['specific_users'] = 'required|array';
            $rules['specific_users.*'] = 'exists:users,id';
        }

        if ($user->role === 'dosen' && !in_array($request->target_audience, ['mahasiswa', 'specific'])) {
             abort(403, 'Akses audiens ditolak.');
        }

        $validated = $request->validate($rules);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_audience' => $validated['target_audience'],
        ]);

        if ($validated['target_audience'] === 'specific') {
            $announcement->targetedUsers()->sync($validated['specific_users']);
        } else {
            // Detach if switched to a broadcast type
            $announcement->targetedUsers()->detach();
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
         if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
             abort(403);
         }
         
         $announcement->delete();
         return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
