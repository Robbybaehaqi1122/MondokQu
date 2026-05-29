<?php

return [
    'trial_days' => env('SAAS_TRIAL_DAYS', 14),
    'grace_days' => env('SAAS_GRACE_DAYS', 5),
    'default_plan' => env('SAAS_DEFAULT_PLAN', 'trial'),
    'trial_warning_days' => env('SAAS_TRIAL_WARNING_DAYS', 3),
    'grace_warning_days' => env('SAAS_GRACE_WARNING_DAYS', 3),
];
