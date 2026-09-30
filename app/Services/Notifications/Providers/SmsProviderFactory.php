<?php

namespace App\Services\Notifications\Providers;

class SmsProviderFactory
{
    public static function make(): SmsProviderInterface
    {
        $driver = config('notifications.channels.sms.driver', 'semaphore');

        return match ($driver) {
            'semaphore' => app(SemaphoreProvider::class),
            'twilio' => app(TwilioProvider::class),
            default => throw new \InvalidArgumentException("Unsupported SMS driver: {$driver}"),
        };
    }
}
