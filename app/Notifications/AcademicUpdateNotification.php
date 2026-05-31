<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class AcademicUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $title;
    private $message;
    private $actionUrl;

    public function __construct($title, $message, $actionUrl)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->icon('/logo.png')
            ->body($this->message)
            ->action('Buka', $this->actionUrl)
            ->data(['url' => $this->actionUrl])
            ->options(['TTL' => 1000]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->actionUrl,
            'type' => 'academic_update'
        ];
    }
}
