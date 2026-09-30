<?php

namespace App\Services\Notifications;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RuleEngine
{
    /**
     * Evaluates rules to determine if and how a notification should be delivered.
     */
    public function evaluate(string $event, array $eventConfig, User $recipient): array
    {
        $deliveryPlan = [
            'channels' => $eventConfig['channels'] ?? [],
        ];

        // 1. Load recipient preferences
        $prefs = $recipient->notificationPreference;

        if (! $prefs) {
            // Default rules if no preferences exist
            return $deliveryPlan;
        }

        // 2. Check event-specific opt-outs
        $optedOutEvents = $prefs->opted_out_events ?? [];
        if (in_array($event, $optedOutEvents, true) && ($eventConfig['priority'] ?? 'medium') !== 'critical') {
            Log::info("User {$recipient->id} opted out of event {$event}");
            $deliveryPlan['channels'] = [];

            return $deliveryPlan;
        }

        // 3. Filter channels based on preferences
        $filteredChannels = [];
        foreach ($deliveryPlan['channels'] as $channel) {
            if ($channel === 'sms' && ! $prefs->sms_enabled) {
                continue;
            }
            if ($channel === 'email' && ! $prefs->email_enabled) {
                continue;
            }
            if ($channel === 'in_app' && ! $prefs->in_app_enabled) {
                continue;
            }
            $filteredChannels[] = $channel;
        }

        // 4. Check Quiet Hours (only for non-critical events and specific channels like SMS)
        if (($eventConfig['priority'] ?? 'medium') !== 'critical' && $this->isInQuietHours($prefs)) {
            // Remove SMS if in quiet hours, maybe fallback to in_app only or delay
            $filteredChannels = array_filter($filteredChannels, fn ($c) => $c !== 'sms');
        }

        $deliveryPlan['channels'] = array_values($filteredChannels);

        return $deliveryPlan;
    }

    private function isInQuietHours(NotificationPreference $prefs): bool
    {
        if (! $prefs->quiet_hours_start || ! $prefs->quiet_hours_end) {
            return false;
        }

        $now = now()->format('H:i');

        // Handle overnight quiet hours (e.g., 22:00 to 08:00)
        if ($prefs->quiet_hours_start > $prefs->quiet_hours_end) {
            return $now >= $prefs->quiet_hours_start || $now <= $prefs->quiet_hours_end;
        }

        // Handle same-day quiet hours (e.g., 14:00 to 16:00)
        return $now >= $prefs->quiet_hours_start && $now <= $prefs->quiet_hours_end;
    }
}
