<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $dashboard = match($request->user()->role) {
                'admin' => route('admin.dashboard', absolute: false),
                'dosen' => route('dosen.dashboard', absolute: false),
                'mahasiswa' => route('mahasiswa.dashboard', absolute: false),
                default => '/',
            };
            return redirect()->intended($dashboard.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $dashboard = match($request->user()->role) {
            'admin' => route('admin.dashboard', absolute: false),
            'dosen' => route('dosen.dashboard', absolute: false),
            'mahasiswa' => route('mahasiswa.dashboard', absolute: false),
            default => '/',
        };

        return redirect()->intended($dashboard.'?verified=1');
    }
}
