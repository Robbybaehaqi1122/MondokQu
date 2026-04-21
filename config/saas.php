<?php

return [
    'trial_days' => env('SAAS_TRIAL_DAYS', 14),
    'grace_days' => env('SAAS_GRACE_DAYS', 5),
    'default_plan' => env('SAAS_DEFAULT_PLAN', 'trial'),
];
