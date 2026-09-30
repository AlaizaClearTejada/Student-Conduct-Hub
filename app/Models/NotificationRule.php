<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'event_type',
        'condition_json',
        'recipient_roles',
        'channels',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'condition_json' => 'array',
            'recipient_roles' => 'array',
            'channels' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
