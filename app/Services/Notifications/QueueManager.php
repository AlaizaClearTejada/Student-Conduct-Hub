<?php

namespace App\Services\Notifications;

use App\Jobs\SendNotificationJob;
use App\Models\Notification;

class QueueManager
{
    /**
     * Enqueue a notification for asynchronous delivery.
     */
    public function enqueue(Notification $notification): void
    {
        $notification->update(['status' => 'queued']);

        SendNotificationJob::dispatch($notification)
            ->onConnection(config('notifications.queue.connection'))
            ->onQueue(config('notifications.queue.driver'));
    }
}
