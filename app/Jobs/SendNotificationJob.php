<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function backoff(): array
    {
        // 1s, 5s, 15s backoff
        return [1, 5, 15];
    }

    public function __construct(
        private Notification $notification
    ) {}

    public function handle(NotificationService $service): void
    {
        try {
            $service->sendNotification($this->notification);
        } catch (\Exception $e) {
            if ($this->attempts() <= $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 15);
            } else {
                $this->notification->update([
                    'status' => 'failed',
                    'failed_reason' => substr($e->getMessage(), 0, 500),
                ]);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Notification send failed completely', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        $this->notification->update([
            'status' => 'failed',
            'failed_reason' => substr($exception->getMessage(), 0, 500),
            'retry_count' => $this->attempts(),
        ]);
    }
}
