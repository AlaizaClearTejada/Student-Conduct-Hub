<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use App\Services\Notifications\Providers\SmsProviderFactory;

class SmsChannel implements ChannelInterface
{
    public function send(Notification $notification): array
    {
        $recipient = $notification->recipient;
        $phoneNumber = $recipient->notificationPreference?->phone_number;

        if (! $phoneNumber) {
            throw new \Exception('Recipient has no phone number configured.');
        }

        $provider = SmsProviderFactory::make();

        return $provider->sendSms($phoneNumber, $notification->body);
    }
}
