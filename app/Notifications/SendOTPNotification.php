<?php

namespace App\Notifications;

use App\Mail\OtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class SendOTPNotification extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        return Mail::to($notifiable->email)
            ->send(new OtpMail($notifiable->otp_code, $notifiable->name));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
