<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller login khusus portal admin (terpisah dari login umum).
class LoginController extends Controller
{
    /**
     * Tampilkan halaman login admin.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Proses permintaan login admin. Setelah autentikasi & regenerasi sesi,
     * tolak (logout paksa) bila user yang masuk ternyata bukan admin.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Portal admin hanya untuk peran admin; selain itu langsung dikeluarkan.
        if (Auth::user()->role !== 'admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Access denied. You are not authorized to access the Admin Portal.',
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
