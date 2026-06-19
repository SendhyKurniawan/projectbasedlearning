<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi email berisi kode OTP 6 digit untuk verifikasi saat registrasi. Antre (queued).
class OtpVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code)
    {
    }

    // Kirim lewat channel email.
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Email - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Terima kasih telah mendaftar di ' . config('app.name') . '.')
            ->line('Gunakan kode berikut untuk memverifikasi alamat email Anda:')
            ->line('# ' . $this->code)
            ->line('Kode ini akan **kadaluarsa dalam 10 menit**.')
            ->line('Jika Anda tidak melakukan pendaftaran, abaikan email ini.')
            ->salutation('Salam, ' . config('app.name'));
    }
}
