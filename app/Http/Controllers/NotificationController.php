<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller notifikasi in-app: daftar notifikasi user & aksi tandai-sudah-dibaca.
class NotificationController extends Controller
{
    /**
     * Tampilkan daftar notifikasi milik user (paginasi 15).
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Tandai satu notifikasi sebagai dibaca, lalu arahkan ke URL-nya bila tersedia.
     */
    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }
        
        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Tandai satu notifikasi sebagai dibaca (tanpa redirect).
     */
    public function markRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        return back();
    }

    /**
     * Tandai semua notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back();
    }
}
