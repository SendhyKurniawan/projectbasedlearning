<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

// Controller pengumuman (resource bersama untuk admin & dosen). Visibilitas & target audiens
// disesuaikan per peran; mendukung lampiran (gambar/PDF) dan pengiriman notifikasi.
class AnnouncementController extends Controller
{
    // Daftar pengumuman yang boleh dilihat user (difilter per peran), plus pencarian & filter target.
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Announcement::with('author')->latest();

        if ($user->role === 'mahasiswa') {
            $query->where(function ($q) use ($user) {
                $q->whereIn('target_audience', ['all', 'mahasiswa'])
                  ->orWhereHas('targetedUsers', function ($subq) use ($user) {
                      $subq->where('user_id', $user->id);
                  });
            });
        } elseif ($user->role === 'dosen') {
             $query->where(function ($q) use ($user) {
                $q->whereIn('target_audience', ['all', 'dosen'])
                  ->orWhere('user_id', $user->id)
                  ->orWhereHas('targetedUsers', function ($subq) use ($user) {
                      $subq->where('user_id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('target') && in_array($request->target, ['all', 'dosen', 'mahasiswa', 'specific'])) {
            $query->where('target_audience', $request->target);
        }

        $announcements = $query->paginate(10)->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    // Form buat pengumuman. Pilihan target audiens dibatasi sesuai peran (admin vs dosen).
    public function create()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $targets = ['all' => 'Semua Pengguna', 'dosen' => 'Seluruh Dosen', 'mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Pengguna Spesifik'];
            $users = User::where('id', '!=', $user->id)->select('id', 'name', 'role')->get();
        } else if ($user->role === 'dosen') {
            $targets = ['mahasiswa' => 'Seluruh Mahasiswa', 'specific' => 'Mahasiswa Spesifik'];
            $users = User::where('role', 'mahasiswa')->select('id', 'name', 'role')->get();
        } else {
            abort(403, 'Akses ditolak.');
        }

        return view('announcements.create', compact('targets', 'users'));
    }

    // Simpan pengumuman (opsional lampiran), set target spesifik bila ada, lalu kirim notifikasi.
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,dosen,mahasiswa,specific',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
        ];

        if ($request->target_audience === 'specific') {
            $rules['specific_users'] = 'required|array';
            $rules['specific_users.*'] = 'exists:users,id';
        }

        if ($user->role === 'dosen') {
             if (!in_array($request->target_audience, ['mahasiswa', 'specific'])) {
                 abort(403, 'Akses audiens ditolak.');
             }
        }

        $validated = $request->validate($rules);

        $data = [
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_audience' => $validated['target_audience'],
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['attachment_path'] = $file->storeAs('announcements', $filename, 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getClientMimeType();
        }

        $announcement = Announcement::create($data);

        if ($validated['target_audience'] === 'specific') {
            $announcement->targetedUsers()->sync($validated['specific_users']);
        }

        // Tentukan penerima notifikasi sesuai target audiens.
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

    // Tampilkan detail pengumuman; cek hak akses sesuai peran & target audiens.
    public function show(Announcement $announcement)
    {
        $user = Auth::user();

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

    // Form edit pengumuman (hanya pembuat atau admin).
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

    // Perbarui pengumuman (hanya pembuat/admin); bisa mengganti atau menghapus lampiran.
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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'remove_attachment' => 'nullable|boolean',
        ];

        if ($request->target_audience === 'specific') {
            $rules['specific_users'] = 'required|array';
            $rules['specific_users.*'] = 'exists:users,id';
        }

        if ($user->role === 'dosen' && !in_array($request->target_audience, ['mahasiswa', 'specific'])) {
             abort(403, 'Akses audiens ditolak.');
        }

        $validated = $request->validate($rules);

        $updateData = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_audience' => $validated['target_audience'],
        ];

        // Hapus lampiran lama bila diminta hapus, atau bila ada lampiran baru menggantikan.
        $shouldRemove = $request->boolean('remove_attachment');
        $hasNewFile = $request->hasFile('attachment');

        if (($shouldRemove || $hasNewFile) && $announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
            $updateData['attachment_path'] = null;
            $updateData['attachment_name'] = null;
            $updateData['attachment_mime'] = null;
        }

        if ($hasNewFile) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $updateData['attachment_path'] = $file->storeAs('announcements', $filename, 'public');
            $updateData['attachment_name'] = $file->getClientOriginalName();
            $updateData['attachment_mime'] = $file->getClientMimeType();
        }

        $announcement->update($updateData);

        if ($validated['target_audience'] === 'specific') {
            $announcement->targetedUsers()->sync($validated['specific_users']);
        } else {
            $announcement->targetedUsers()->detach();
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    // Hapus pengumuman beserta lampirannya (hanya pembuat/admin).
    public function destroy(Announcement $announcement)
    {
         if (Auth::id() !== $announcement->user_id && Auth::user()->role !== 'admin') {
             abort(403);
         }

         if ($announcement->attachment_path) {
             Storage::disk('public')->delete($announcement->attachment_path);
         }

         $announcement->delete();
         return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
