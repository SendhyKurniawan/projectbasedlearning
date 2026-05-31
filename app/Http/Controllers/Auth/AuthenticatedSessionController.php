<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        // Block inactive accounts: distinguish OTP-pending (offer resend)
        // from admin-approval-pending (dosen waiting).
        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($user->otp_verified_at === null) {
                // Stash the user so they can land on /verify-otp and resend a code.
                $request->session()->put('otp_user_id', $user->id);

                return redirect()->route('verification.otp')
                    ->with('status', 'Email Anda belum diverifikasi. Silakan masukkan kode verifikasi atau kirim ulang kode baru.');
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum diaktifkan. Silakan hubungi admin untuk konfirmasi.']);
        }

        $request->session()->regenerate();

        $dashboard = match($user->role) {
            'admin'     => route('admin.dashboard'),
            'dosen'     => route('dosen.dashboard'),
            'mahasiswa' => route('mahasiswa.dashboard'),
            default     => '/',
        };

        return redirect()->intended($dashboard);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
