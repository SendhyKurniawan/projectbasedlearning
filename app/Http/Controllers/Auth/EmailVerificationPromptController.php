<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            $dashboard = match($request->user()->role) {
                'admin' => route('admin.dashboard', absolute: false),
                'dosen' => route('dosen.dashboard', absolute: false),
                'mahasiswa' => route('mahasiswa.dashboard', absolute: false),
                default => '/',
            };
            return redirect()->intended($dashboard);
        }

        return view('auth.verify-email');
    }
}
