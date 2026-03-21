<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $studentClasses = \App\Models\StudentClass::all();
        return view('auth.register', compact('studentClasses'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role'     => ['required', 'in:mahasiswa,dosen'],
            'nim'      => ['required_if:role,mahasiswa', 'nullable', 'string', 'max:20', 'unique:users,nim'],
            'nip'      => ['required_if:role,dosen',     'nullable', 'string', 'max:20', 'unique:users,nip'],
            'student_class_id' => ['required_if:role,mahasiswa', 'nullable', 'exists:student_classes,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nim.required_if' => 'NIM wajib diisi untuk mahasiswa.',
            'nim.unique'      => 'NIM sudah terdaftar.',
            'nip.required_if' => 'NIP wajib diisi untuk dosen.',
            'nip.unique'      => 'NIP sudah terdaftar.',
            'student_class_id.required_if' => 'Kode Kelas wajib dipilih untuk mahasiswa.',
            'student_class_id.exists'      => 'Kode Kelas tidak valid.',
        ]);

        $isDosen = $request->role === 'dosen';

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'nim'       => $request->role === 'mahasiswa' ? $request->nim : null,
            'nip'       => $request->role === 'dosen'     ? $request->nip : null,
            'student_class_id' => $request->role === 'mahasiswa' ? $request->student_class_id : null,
            // Dosen requires admin approval; set inactive until approved
            'is_active' => !$isDosen,
        ]);

        event(new Registered($user));

        if ($isDosen) {
            // Do NOT auto-login; redirect to login with a pending-approval notice
            return redirect()->route('login')
                ->with('status', 'Akun dosen Anda berhasil dibuat dan sedang menunggu persetujuan admin. Anda akan dapat login setelah akun diaktifkan.');
        }

        // Mahasiswa: login immediately
        Auth::login($user);

        return redirect()->route('mahasiswa.dashboard');
    }
}
