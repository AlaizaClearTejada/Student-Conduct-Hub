<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sms_enabled',
        'email_enabled',
        'in_app_enabled',
        'max_sms_per_day',
        'max_emails_per_day',
        'quiet_hours_start',
        'quiet_hours_end',
        'opted_out_events',
        'phone_number',
        'verified_phone',
        'secondary_email',
    ];

    protected function casts(): array
    {
        return [
            'sms_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'verified_phone' => 'boolean',
            'opted_out_events' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
