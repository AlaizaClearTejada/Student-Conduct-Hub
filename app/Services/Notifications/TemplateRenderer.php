<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Str;

class TemplateRenderer
{
    /**
     * Renders a notification template with contextual data.
     */
    public function render(string $templateKey, array $context, User $recipient): array
    {
        // 1. Fetch template from DB or fallback to file-based config
        $templateRecord = NotificationTemplate::where('key', $templateKey)->first();

        // Prepare variables for interpolation
        $variables = array_merge($context, [
            'recipient_name' => $recipient->first_name.' '.$recipient->last_name,
            'recipient_email' => $recipient->email,
        ]);

        if ($templateRecord) {
            $subject = $templateRecord->email_subject;
            $body = $templateRecord->sms_template ?? $templateRecord->email_template;
        } else {
            // Fallback simplistic rendering if no DB template is found
            $subject = $this->interpolate(config("notifications.templates_fallback.{$templateKey}.subject", 'Notification: '.Str::headline($templateKey)), $variables);
            $body = $this->interpolate(config("notifications.templates_fallback.{$templateKey}.body", 'You have a new notification regarding Case #{{case_id}}.'), $variables);

            return [
                'subject' => $subject,
                'body' => $body,
            ];
        }

        return [
            'subject' => $this->interpolate($subject ?? '', $variables),
            'body' => $this->interpolate($body ?? '', $variables),
        ];
    }

    private function interpolate(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $text = str_replace('{{'.$key.'}}', (string) $value, $text);
            }
        }

        return $text;
    }
}
