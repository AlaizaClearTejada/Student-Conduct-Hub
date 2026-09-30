<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'sms_template',
        'email_subject',
        'email_template',
        'in_app_template',
        'variables',
        'event_type',
        'priority',
        'channels',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'channels' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
