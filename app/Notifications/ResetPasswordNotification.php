<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Permintaan Reset Password - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->line('Klik tombol di bawah ini untuk membuat password baru:')
            ->action('Reset Password Saya', $resetUrl)
            ->line('Link reset password ini akan **kadaluarsa dalam 60 menit**.')
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Salam, ' . config('app.name'));
    }
}
