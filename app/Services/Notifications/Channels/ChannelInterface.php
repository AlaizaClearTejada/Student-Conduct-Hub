<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;

interface ChannelInterface
{
    /**
     * Sends the notification via the specific channel.
     * Returns an array with 'status', 'external_id', 'contact', and optional 'response'.
     */
    public function send(Notification $notification): array;
}
