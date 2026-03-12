<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $announcementId;
    private $title;
    private $authorName;

    /**
     * Create a new notification instance.
     */
    public function __construct($announcementId, $title, $authorName)
    {
        $this->announcementId = $announcementId;
        $this->title = $title;
        $this->authorName = $authorName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
