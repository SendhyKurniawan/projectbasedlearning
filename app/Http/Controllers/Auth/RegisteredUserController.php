<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'nim'       => $request->role === 'mahasiswa' ? $request->nim : null,
            'nip'       => $request->role === 'dosen'     ? $request->nip : null,
            'student_class_id' => $request->role === 'mahasiswa' ? $request->student_class_id : null,
            // Inactive until OTP verified (and, for dosen, also admin approved)
            'is_active'      => false,
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        event(new Registered($user));

        $user->notify(new OtpVerificationNotification($code));

        // Stash the pending user id in session so the OTP screen knows who to verify
        // without authenticating them.
        $request->session()->put('otp_user_id', $user->id);

        return redirect()->route('verification.otp')
            ->with('status', 'Kami telah mengirim kode verifikasi 6 digit ke ' . $user->email . '.');
    }
}
