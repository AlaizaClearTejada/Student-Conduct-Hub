<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sms_enabled' => true,
            'email_enabled' => true,
            'in_app_enabled' => true,
            'max_sms_per_day' => 5,
            'max_emails_per_day' => 10,
        ];
    }
}
