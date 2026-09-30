<?php

namespace App\Services\Notifications\Providers;

interface SmsProviderInterface
{
    public function sendSms(string $phoneNumber, string $message): array;
}
