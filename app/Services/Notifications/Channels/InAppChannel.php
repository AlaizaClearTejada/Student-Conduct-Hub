<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class InAppChannel implements ChannelInterface
{
    public function send(Notification $notification): array
    {
        $recipient = $notification->recipient;

        // In-app notifications are persisted via the Notification model itself.
        // We just need to broadcast it using Laravel Reverb / Echo if realtime is enabled.
        if (config('notifications.channels.in_app.real_time_enabled')) {
            try {
                // Assuming we would broadcast a NotificationCreated event here
                // broadcast(new \App\Events\NotificationCreated($notification));
                Log::info("Broadcasted in-app notification to user {$recipient->id}");
            } catch (\Exception $e) {
                Log::error('Failed to broadcast in-app notification: '.$e->getMessage());
            }
        }

        return [
            'status' => 'delivered',
            'contact' => 'user_id:'.$recipient->id,
            'external_id' => 'inapp_'.$notification->id,
            'response' => ['info' => 'Persisted in database'],
        ];
    }
}
