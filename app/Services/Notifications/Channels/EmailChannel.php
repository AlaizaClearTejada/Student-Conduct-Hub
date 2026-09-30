<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class EmailChannel implements ChannelInterface
{
    public function send(Notification $notification): array
    {
        $recipient = $notification->recipient;
        $email = $recipient->email;

        if (! $email) {
            throw new \Exception('Recipient has no email address.');
        }

        // We use Laravel's Mail facade.
        // To properly send HTML email, we'd use a Mailable, but we can do raw html too.
        Mail::html($notification->body, function ($message) use ($email, $notification) {
            $message->to($email)
                ->subject($notification->subject);
        });

        return [
            'status' => 'delivered',
            'contact' => $email,
            'external_id' => 'email_'.uniqid(),
            'response' => ['info' => 'Dispatched to Laravel Mailer'],
        ];
    }
}
