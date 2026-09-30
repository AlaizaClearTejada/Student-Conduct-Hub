<?php

namespace App\Services\Notifications\Providers;

use Illuminate\Support\Facades\Http;

class TwilioProvider implements SmsProviderInterface
{
    public function sendSms(string $phoneNumber, string $message): array
    {
        $accountSid = config('notifications.channels.sms.twilio.account_sid');
        $authToken = config('notifications.channels.sms.twilio.auth_token');
        $from = config('notifications.channels.sms.twilio.from');

        if (! $accountSid || ! $authToken) {
            // For testing environments without real API key, simulate success
            return [
                'status' => 'delivered',
                'contact' => $phoneNumber,
                'external_id' => 'simulated_twi_'.uniqid(),
                'response' => ['simulated' => true],
            ];
        }

        // Using Twilio's REST API directly to avoid requiring the SDK if not strictly needed
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $response = Http::asForm()->withBasicAuth($accountSid, $authToken)->post($url, [
            'To' => $phoneNumber,
            'From' => $from,
            'Body' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'status' => 'sent',
                'contact' => $phoneNumber,
                'external_id' => $data['sid'] ?? null,
                'response' => $data,
            ];
        }

        throw new \Exception('Twilio API error: '.$response->body());
    }
}
