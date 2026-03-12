<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        // Count pending dosen accounts for badge notification
        $pendingDosen = User::where('role', 'dosen')->where('is_active', false)->count();

        return view('admin.users.index', compact('users', 'pendingDosen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = StudentClass::with('studyProgram')->get();
        return view('admin.users.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $classes = StudentClass::with('studyProgram')->get();
        return view('admin.users.edit', compact('user', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Toggle active/inactive status of a user (for approving dosen accounts).
     */
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

    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->user_ids;

        // Prevent admin from deleting themselves
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
