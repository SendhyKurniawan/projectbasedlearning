<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class GradeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $assignmentTitle;
    private $courseId;

    public function __construct($assignmentTitle, $courseId)
    {
        $this->assignmentTitle = $assignmentTitle;
        $this->courseId = $courseId;
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('mahasiswa.grades.index');
        return (new WebPushMessage)
            ->title('Tugas Telah Dinilai')
            ->icon('/logo.png')
            ->body("Tugas '{$this->assignmentTitle}' Anda telah dinilai.")
            ->action('Buka', $url)
            ->data(['url' => $url])
            ->options(['TTL' => 1000]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tugas Telah Dinilai',
            'message' => "Tugas '{$this->assignmentTitle}' Anda telah dinilai.",
            'url' => route('mahasiswa.grades.index'),
            'type' => 'grade'
        ];
    }
}
