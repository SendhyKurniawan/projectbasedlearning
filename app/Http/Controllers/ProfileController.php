<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// Controller profil mandiri: tiap user mengubah datanya sendiri, ganti password, atau hapus akun.
class ProfileController extends Controller
{
    /**
     * Tampilkan form profil milik user; mahasiswa diberi data kelas/prodi/jurusan.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        if ($user->role === 'mahasiswa') {
            $user->load('studentClass.studyProgram.department', 'studentClass.semester.academicYear');
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Perbarui data profil. Jika email berubah, reset status verifikasi email.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hapus akun user sendiri; wajib konfirmasi password, lalu logout & bersihkan sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
