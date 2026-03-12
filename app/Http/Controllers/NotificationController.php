<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read and redirect if URL exists.
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
     * Mark a specific notification as read.
     */
    public function markRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back();
    }
}
