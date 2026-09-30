<?php

return [
    'channels' => [
        'sms' => [
            'driver' => env('SMS_DRIVER', 'semaphore'), // semaphore, twilio
            'semaphore' => [
                'api_key' => env('SEMAPHORE_API_KEY'),
                'sender_name' => env('SEMAPHORE_SENDER_NAME', 'CSU SCMS'),
            ],
            'twilio' => [
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM_NUMBER'),
            ],
            'rate_limit' => [
                'per_minute' => 100,
                'per_hour_per_recipient' => 5,
            ],
            'retry_policy' => [
                'max_attempts' => 3,
                'backoff_multiplier' => 2,
                'initial_delay' => 1000, // milliseconds
            ],
        ],
        'email' => [
            'driver' => env('MAIL_MAILER', 'ses'), // ses, sendgrid, smtp
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'notifications@csu-conduct.edu'),
                'name' => env('MAIL_FROM_NAME', 'CSU Conduct Management System'),
            ],
            'rate_limit' => [
                'per_second' => 14,
                'per_day' => 50000,
            ],
            'retry_policy' => [
                'max_attempts' => 5,
                'backoff_multiplier' => 2,
                'initial_delay' => 30000, // milliseconds
            ],
        ],
        'in_app' => [
            'persistence_days' => 30,
            'real_time_enabled' => true,
            'fallback_polling_interval' => 15, // seconds
        ],
    ],

    'events' => [
        'complaint_filed' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['accused', 'tribunal_chair', 'osdw_staff'],
            'priority' => 'high',
            'template' => 'complaint_filed_notification',
        ],
        'case_acknowledged' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['complainant'],
            'priority' => 'high',
            'template' => 'complaint_acknowledged',
        ],
        'hearing_scheduled' => [
            'channels' => ['email', 'sms', 'in_app'],
            'recipients' => ['accused', 'complainant', 'tribunal', 'osdw_staff'],
            'priority' => 'critical',
            'template' => 'hearing_scheduled_notification',
        ],
        'hearing_cancelled' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['accused', 'complainant', 'tribunal', 'osdw_staff'],
            'priority' => 'high',
            'template' => 'hearing_cancelled_notification',
        ],
        'hearing_postponed' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['accused', 'complainant', 'tribunal', 'osdw_staff'],
            'priority' => 'high',
            'template' => 'hearing_postponed_notification',
        ],
        'verdict_issued' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['accused', 'complainant', 'tribunal_chair', 'osdw_staff'],
            'priority' => 'high',
            'template' => 'verdict_notification',
            'security' => 'encrypted',
        ],
        'case_closed' => [
            'channels' => ['email', 'in_app'],
            'recipients' => ['accused', 'complainant', 'tribunal', 'osdw_staff'],
            'priority' => 'medium',
            'template' => 'case_closed_notification',
        ],
    ],

    'templates_path' => resource_path('notifications/templates'),
    'encryption' => [
        'enabled' => true,
        'algorithm' => 'AES-256-GCM',
    ],
    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'redis'),
        'driver' => env('QUEUE_CONNECTION', 'redis'),
    ],
];
