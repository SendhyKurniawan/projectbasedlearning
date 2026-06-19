<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

// Notifikasi adanya pengumuman baru untuk audiens sasaran. Disimpan ke database (in-app).
class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $announcementId;
    private $title;
    private $authorName;

    public function __construct($announcementId, $title, $authorName)
    {
        $this->announcementId = $announcementId;
        $this->title = $title;
        $this->authorName = $authorName;
    }

    // Kirim ke channel database (notifikasi in-app).
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengumuman Baru',
            'message' => "{$this->authorName} membuat pengumuman: {$this->title}",
            'url' => route('announcements.show', $this->announcementId),
            'type' => 'announcement'
        ];
    }
}
