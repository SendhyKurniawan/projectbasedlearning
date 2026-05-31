<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Http\Request;

class PushDebugController extends Controller
{
    public function index()
    {
        $users = User::with('pushSubscriptions')->get();

        return view('admin.debug.push', compact('users'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $title = $request->input('title', 'Test Push Notification');
        $message = $request->input('message', 'Ini adalah pesan percobaan untuk push notification.');

        $user->notify(new TestNotification($title, $message));

        return back()->with('success', "Test push notification sent to {$user->name}.");
    }
}
