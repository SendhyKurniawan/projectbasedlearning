<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
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

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
