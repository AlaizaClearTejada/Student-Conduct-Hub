<?php

namespace App\Services\Notifications\Providers;

use Illuminate\Support\Facades\Http;

class SemaphoreProvider implements SmsProviderInterface
{
    public function sendSms(string $phoneNumber, string $message): array
    {
        $apiKey = config('notifications.channels.sms.semaphore.api_key');
        $senderName = config('notifications.channels.sms.semaphore.sender_name');

        if (! $apiKey) {
            // For testing environments without real API key, simulate success
            return [
                'status' => 'delivered',
                'contact' => $phoneNumber,
                'external_id' => 'simulated_sem_'.uniqid(),
                'response' => ['simulated' => true],
            ];
        }

        $response = Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => $apiKey,
            'number' => $phoneNumber,
            'message' => $message,
            'sendername' => $senderName,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'status' => 'sent',
                'contact' => $phoneNumber,
                'external_id' => $data[0]['message_id'] ?? null,
                'response' => $data,
            ];
        }

        throw new \Exception('Semaphore API error: '.$response->body());
    }
}
