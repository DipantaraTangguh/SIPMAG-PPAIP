<?php

use App\Models\User;

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
    'login_security' => [
        'lock_after_attempts' => (int) env('AUTH_LOCK_AFTER_ATTEMPTS', 5),
        'initial_lock_seconds' => (int) env('AUTH_INITIAL_LOCK_SECONDS', 60),
        'maximum_lock_seconds' => (int) env('AUTH_MAXIMUM_LOCK_SECONDS', 900),
        'dummy_password_hash' => env(
            'AUTH_DUMMY_PASSWORD_HASH',
            '$2y$12$EmVBvzwCLgHwdc1M.TvzeeoHlA47taVBJJzx/KfiGs8nAR4GBP/Fy'
        ),
    ],

];
