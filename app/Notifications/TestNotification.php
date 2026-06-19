<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

// Notifikasi percobaan untuk menguji pengiriman push (dipakai halaman debug admin).
// Catatan: tidak meng-implements ShouldQueue, jadi dikirim sinkron.
class TestNotification extends Notification
{
    use Queueable;

    private $title;
    private $message;

    public function __construct($title = 'Test Push Notification', $message = 'Ini adalah pesan percobaan untuk push notification.')
    {
        $this->title = $title;
        $this->message = $message;
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
            ->options(['TTL' => 1000]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => '#',
            'type' => 'test'
        ];
    }
}
