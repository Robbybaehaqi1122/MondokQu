<?php

return [
    'trial_days' => env('SAAS_TRIAL_DAYS', 14),
    'grace_days' => env('SAAS_GRACE_DAYS', 5),
    'default_plan' => env('SAAS_DEFAULT_PLAN', 'trial'),
    'trial_warning_days' => env('SAAS_TRIAL_WARNING_DAYS', 3),
    'grace_warning_days' => env('SAAS_GRACE_WARNING_DAYS', 3),

    'limits' => [
        'max_users' => env('SAAS_MAX_USERS', 50),
        'max_santri' => env('SAAS_MAX_SANTRI', 200),
        'max_storage_mb' => env('SAAS_MAX_STORAGE_MB', 1024),
    ],
];
