<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtp extends Notification
{
    public function __construct(public string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Atur Ulang Kata Sandi — Wisary Laundry')
            ->greeting('Halo,')
            ->line('Anda menerima email ini karena ada permintaan untuk mengatur ulang kata sandi akun Wisary Laundry.')
            ->line('Masukkan kode berikut di halaman Lupa Kata Sandi:')
            ->line('**'.$this->code.'**')
            ->line('Kode berlaku selama '.config('auth.passwords.users.expire').' menit dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak merasa meminta ini, abaikan email ini dan jangan bagikan kode kepada siapa pun.')
            ->salutation('Salam,');
    }
}