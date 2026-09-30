<?php

namespace App\Listeners;

use App\Events\CaseEvents\ComplaintFiled;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendComplaintNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(ComplaintFiled $event): void
    {
        $this->notificationService->dispatchNotification(
            event: 'complaint_filed',
            context: [
                'case_id' => $event->case->id,
                'complainant_id' => $event->complainant->id,
                'description' => $event->description,
            ]
        );
    }
}
