<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inline Export Threshold
    |--------------------------------------------------------------------------
    |
    | Exports up to this number of rows are streamed immediately. Larger exports
    | are queued and written to storage so the HTTP request does not hang.
    |
    */
    'inline_threshold' => (int) env('EXPORT_INLINE_THRESHOLD', 5000),

    /*
    |--------------------------------------------------------------------------
    | Export Storage
    |--------------------------------------------------------------------------
    */
    'disk' => env('EXPORT_DISK', 'local'),
    'retention_days' => (int) env('EXPORT_RETENTION_DAYS', 7),
];
