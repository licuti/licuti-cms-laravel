<?php

return [
    // Null follows the application's asynchronous queue; sync/deferred falls back to database.
    'connection' => null,
    'queue' => 'default',
    'max_attempts' => 3,
    'backoff' => [60, 300],
    // Keep SMTP < job < lease < the selected queue connection's retry_after (normally 90).
    'smtp_timeout' => 15,
    'job_timeout' => 30,
    'lease_seconds' => 60,
    'recovery_batch' => 200,
    'enqueue_cooldown' => 300,
    'recipient_batch' => 200,
    // Null follows mail.default, including the array mailer during tests.
    'mailer' => null,
    'retention' => [
        'read_inbox_days' => 90,
        'payload_days' => 90,
        'dedup_days' => 365,
        'batch' => 500,
    ],
];
