<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Http\Request;

// Controller alat bantu debug: mengirim push notification percobaan ke seorang user.
class PushDebugController extends Controller
{
    // Tampilkan daftar user beserta langganan push (subscription) mereka.
    public function index()
    {
        $users = User::with('pushSubscriptions')->get();

        return view('admin.debug.push', compact('users'));
    }

    // Kirim notifikasi percobaan ke user terpilih (judul & pesan opsional).
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
