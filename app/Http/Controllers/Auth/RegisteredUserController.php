<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

// Controller registrasi mandiri (mahasiswa/dosen). Akun dibuat nonaktif + dikirimi OTP;
// mahasiswa juga otomatis di-enroll ke matkul kelasnya.
class RegisteredUserController extends Controller
{
    // Tampilkan form registrasi. Hanya tawarkan kelas di semester yang sedang aktif.
    public function create(): View
    {
        // Hanya tampilkan kelas pada semester aktif — mahasiswa baru bergabung ke
        // semester berjalan (dan otomatis di-enroll ke matkulnya saat mendaftar).
        $studentClasses = \App\Models\StudentClass::whereHas('semester', fn ($q) => $q->where('is_active', true))
            ->with('studyProgram:id,name')
            ->orderBy('study_program_id')
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('studentClasses'));
    }

    // Proses registrasi: validasi sesuai peran, buat akun nonaktif + OTP, auto-enroll
    // mahasiswa, lalu arahkan ke halaman verifikasi OTP.
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

        // Buat kode OTP 6 digit.
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'nim'       => $request->role === 'mahasiswa' ? $request->nim : null,
            'nip'       => $request->role === 'dosen'     ? $request->nip : null,
            'student_class_id' => $request->role === 'mahasiswa' ? $request->student_class_id : null,
            // Nonaktif sampai OTP diverifikasi (dan untuk dosen, juga disetujui admin).
            'is_active'      => false,
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Auto-enroll mahasiswa baru ke matkul kelasnya (plus matkul umum semester
        // tersebut) agar dashboard tidak kosong saat login pertama. student_class_id
        // pada course adalah kelas tempat matkul itu diikat di admin Akademik
        // ("Mata Kuliah Khusus Kelas").
        if ($user->role === 'mahasiswa' && $user->student_class_id) {
            $semesterId = optional($user->studentClass)->semester_id;

            $courseIds = Course::where(function ($q) use ($user, $semesterId) {
                $q->where('student_class_id', $user->student_class_id);
                if ($semesterId) {
                    $q->orWhere(fn ($w) => $w->whereNull('student_class_id')->where('semester_id', $semesterId));
                }
            })->pluck('id');

            if ($courseIds->isNotEmpty()) {
                $user->enrollments()->syncWithoutDetaching(
                    $courseIds->mapWithKeys(fn ($id) => [$id => ['enrolled_at' => now()]])->all()
                );
            }
        }

        event(new Registered($user));

        $user->notify(new OtpVerificationNotification($code));

        // Halaman OTP membaca id ini tanpa mengautentikasi user.
        $request->session()->put('otp_user_id', $user->id);

        return redirect()->route('verification.otp')
            ->with('status', 'Kami telah mengirim kode verifikasi 6 digit ke ' . $user->email . '.');
    }
}
