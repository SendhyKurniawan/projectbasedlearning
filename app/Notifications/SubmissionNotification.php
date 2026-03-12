<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $assignmentTitle;
    private $actionUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($studentName, $assignmentTitle, $actionUrl)
    {
        $this->studentName = $studentName;
        $this->assignmentTitle = $assignmentTitle;
        $this->actionUrl = $actionUrl;
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
            'title' => 'Pengumpulan Tugas Baru',
            'message' => "{$this->studentName} telah mengumpulkan tugas: {$this->assignmentTitle}",
            'url' => $this->actionUrl,
            'type' => 'submission'
        ];
    }
}
