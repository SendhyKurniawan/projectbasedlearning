<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpVerificationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', ['email' => $user->email]);
    }

    // Verify the OTP, then activate (mahasiswa) or hand off to admin approval (dosen).
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.digits' => 'Kode verifikasi harus 6 digit angka.',
        ]);

        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['code' => 'Sesi verifikasi tidak ditemukan. Silakan daftar ulang.']);
        }

        if (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'Kode telah kadaluarsa. Silakan kirim ulang kode baru.',
            ]);
        }

        if (!hash_equals((string) $user->otp_code, (string) $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid.',
            ]);
        }

        $isDosen = $user->role === 'dosen';

        $user->forceFill([
            'otp_verified_at'   => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            // Mahasiswa is fully activated; dosen still needs admin approval.
            'is_active'         => !$isDosen,
        ])->save();

        $request->session()->forget('otp_user_id');

        if ($isDosen) {
            return redirect()->route('login')
                ->with('status', 'Email berhasil diverifikasi. Akun dosen Anda sedang menunggu persetujuan admin sebelum dapat digunakan.');
        }

        Auth::login($user);

        return redirect()->route('mahasiswa.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('register');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        $user->notify(new OtpVerificationNotification($code));

        return back()->with('status', 'Kode verifikasi baru telah dikirim ke ' . $user->email . '.');
    }

    protected function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('otp_user_id');
        if (!$id) {
            return null;
        }

        return User::find($id);
    }
}
