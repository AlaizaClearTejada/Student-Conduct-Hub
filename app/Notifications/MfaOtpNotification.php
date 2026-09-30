<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MfaOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SCMS Security Verification Code')
            ->greeting('Hello, '.($notifiable->first_name ?? $notifiable->name).'!')
            ->line('Your one-time verification code for the Student Conduct Management System is:')
            ->line('**'.$this->otp.'**')
            ->line('This code will expire in 10 minutes. Do not share this code with anyone.')
            ->line('If you did not request this code, please contact the system administrator immediately.')
            ->salutation('— SCMS Security Team');
    }
}
