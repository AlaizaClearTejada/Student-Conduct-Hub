<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\TribunalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'event_type' => 'test_event',
            'case_id' => TribunalCase::factory(),
            'recipient_id' => User::factory(),
            'recipient_type' => 'student',
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'channels' => ['email', 'sms'],
            'status' => 'pending',
            'priority' => 'medium',
            'is_encrypted' => false,
        ];
    }
}
