<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'case_id',
        'recipient_id',
        'recipient_type',
        'subject',
        'body',
        'channels',
        'template_key',
        'template_data',
        'priority',
        'is_encrypted',
        'encrypted_body',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_reason',
        'retry_count',
        'max_retries',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'template_data' => 'array',
            'is_encrypted' => 'boolean',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(TribunalCase::class, 'case_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function channelLogs(): HasMany
    {
        return $this->hasMany(NotificationChannelLog::class);
    }
}
