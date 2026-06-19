<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller sesi login umum (mahasiswa/dosen). Memblokir akun nonaktif dan mengarahkan
// user ke dashboard sesuai perannya setelah login.
class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses permintaan login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        // Blokir akun nonaktif: bedakan yang masih menunggu OTP (tawarkan kirim ulang)
        // dari yang menunggu persetujuan admin (dosen).
        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($user->otp_verified_at === null) {
                // Simpan user agar bisa mendarat di /verify-otp dan mengirim ulang kode.
                $request->session()->put('otp_user_id', $user->id);

                return redirect()->route('verification.otp')
                    ->with('status', 'Email Anda belum diverifikasi. Silakan masukkan kode verifikasi atau kirim ulang kode baru.');
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum diaktifkan. Silakan hubungi admin untuk konfirmasi.']);
        }

        $request->session()->regenerate();

        // Tujuan setelah login ditentukan oleh peran user.
        $dashboard = match($user->role) {
            'admin'     => route('admin.dashboard'),
            'dosen'     => route('dosen.dashboard'),
            'mahasiswa' => route('mahasiswa.dashboard'),
            default     => '/',
        };

        return redirect()->intended($dashboard);
    }

    /**
     * Akhiri sesi (logout) dan bersihkan token sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
