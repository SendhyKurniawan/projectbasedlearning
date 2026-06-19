<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Controller manajemen pengguna oleh admin: CRUD user + persetujuan akun dosen + hapus massal.
class UserController extends Controller
{
    // Daftar user dengan filter pencarian, role, dan status aktif; juga hitung dosen pending.
    public function index(Request $request)
    {
        $query = User::with('studentClass:id,name,study_program_id', 'studentClass.studyProgram:id,name,department_id,level')->select('id', 'name', 'email', 'nim', 'nip', 'role', 'is_active', 'student_class_id', 'created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $pendingDosen = User::where('role', 'dosen')->where('is_active', false)->count();

        return view('admin.users.index', compact('users', 'pendingDosen'));
    }

    // Form tambah user (sediakan daftar kelas untuk mahasiswa).
    public function create()
    {
        $classes = StudentClass::with('studyProgram')->get();
        return view('admin.users.create', compact('classes'));
    }

    // Simpan user baru: hash password, langsung aktif (is_active = true).
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role'     => ['required', 'string', 'in:admin,dosen,mahasiswa'],
            'nim'      => ['nullable', 'string', 'max:20', 'unique:users,nim'],
            'nip'      => ['nullable', 'string', 'max:20', 'unique:users,nip'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    // Form edit user.
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $classes = StudentClass::with('studyProgram')->get();
        return view('admin.users.edit', compact('user', 'classes'));
    }

    // Perbarui user; password hanya di-hash & diubah bila diisi (selain itu dibiarkan).
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'      => ['required', 'string', 'in:admin,dosen,mahasiswa'],
            'nim'       => ['nullable', 'string', 'max:20', 'unique:users,nim,' . $user->id],
            'nip'       => ['nullable', 'string', 'max:20', 'unique:users,nip,' . $user->id],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
            'is_active' => ['boolean'],
            'password'  => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Jika password diisi → hash; jika kosong → jangan ubah password lama.
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    // Aktif/nonaktifkan akun (sekaligus menyetujui akun dosen yang masih pending).
    // Tidak boleh mengubah status akun sendiri.
    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $msg = $user->is_active
            ? "Akun {$user->name} berhasil diaktifkan."
            : "Akun {$user->name} berhasil dinonaktifkan.";

        return back()->with('success', $msg);
    }

    // Hapus user (tidak boleh menghapus akun sendiri).
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    // Hapus banyak user sekaligus; akun sendiri otomatis dikecualikan dari daftar.
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->user_ids;

        if (in_array(auth()->id(), $userIds)) {
            $userIds = array_diff($userIds, [auth()->id()]);
        }

        if (empty($userIds)) {
            return back()->with('error', 'Tidak ada user valid yang dapat dihapus.');
        }

        User::whereIn('id', $userIds)->delete();

        return redirect()->route('admin.users.index')
            ->with('success', count($userIds) . ' user berhasil dihapus.');
    }
}
