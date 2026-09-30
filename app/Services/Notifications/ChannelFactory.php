<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Channels\ChannelInterface;
use App\Services\Notifications\Channels\EmailChannel;
use App\Services\Notifications\Channels\InAppChannel;
use App\Services\Notifications\Channels\SmsChannel;

class ChannelFactory
{
    public static function make(string $channel): ChannelInterface
    {
        return match ($channel) {
            'sms' => app(SmsChannel::class),
            'email' => app(EmailChannel::class),
            'in_app' => app(InAppChannel::class),
            default => throw new \InvalidArgumentException("Unsupported channel: {$channel}"),
        };
    }
}
