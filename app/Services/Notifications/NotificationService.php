<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private RuleEngine $ruleEngine,
        private TemplateRenderer $templateRenderer,
        private RecipientMapper $recipientMapper,
        private QueueManager $queueManager
    ) {}

    /**
     * Dispatch an event to be processed into notifications.
     */
    public function dispatchNotification(string $event, array $context): void
    {
        try {
            // 1. Get base config for this event
            $eventConfig = config("notifications.events.{$event}");

            if (! $eventConfig) {
                Log::warning("No notification config found for event: {$event}");

                return;
            }

            // 2. Map recipients based on roles in config and context
            $recipients = $this->recipientMapper->map($eventConfig['recipients'], $context);

            foreach ($recipients as $recipient) {
                // 3. Evaluate dynamic rules (opt-outs, channel preferences, custom rules)
                $deliveryPlan = $this->ruleEngine->evaluate($event, $eventConfig, $recipient);

                if (empty($deliveryPlan['channels'])) {
                    continue; // Suppressed or opted out of all channels
                }

                // 4. Render template
                $rendered = $this->templateRenderer->render($eventConfig['template'], $context, $recipient);

                // 5. Persist to DB
                $notification = Notification::create([
                    'event_type' => $event,
                    'case_id' => $context['case_id'] ?? null,
                    'recipient_id' => $recipient->id,
                    'recipient_type' => $this->recipientMapper->determineRole($recipient, $context),
                    'subject' => $rendered['subject'],
                    'body' => $rendered['body'],
                    'channels' => $deliveryPlan['channels'],
                    'template_key' => $eventConfig['template'],
                    'template_data' => $context,
                    'priority' => $eventConfig['priority'] ?? 'medium',
                    'status' => 'pending',
                ]);

                // 6. Queue for delivery
                $this->queueManager->enqueue($notification);
            }
        } catch (\Exception $e) {
            Log::error("Failed to dispatch notification for event {$event}", [
                'error' => $e->getMessage(),
                'context' => $context,
            ]);
        }
    }

    /**
     * Actually send the notification via channels (called by Job).
     */
    public function sendNotification(Notification $notification): void
    {
        $notification->update(['status' => 'queued']);

        $channels = $notification->channels;
        $failedCount = 0;

        foreach ($channels as $channelName) {
            try {
                $channel = ChannelFactory::make($channelName);
                $result = $channel->send($notification);

                $notification->channelLogs()->create([
                    'channel' => $channelName,
                    'status' => $result['status'],
                    'external_id' => $result['external_id'] ?? null,
                    'recipient_contact' => $result['contact'],
                    'provider_response' => $result['response'] ?? null,
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                $failedCount++;
                $notification->channelLogs()->create([
                    'channel' => $channelName,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'recipient_contact' => 'unknown',
                ]);
                Log::error("Channel {$channelName} failed to send notification ID {$notification->id}");
            }
        }

        if ($failedCount === count($channels)) {
            throw new \Exception('All channels failed to send notification.');
        }

        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
