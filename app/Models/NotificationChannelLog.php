<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'channel',
        'external_id',
        'status',
        'recipient_contact',
        'provider_response',
        'error_message',
        'cost',
        'sent_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'cost' => 'decimal:4',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
