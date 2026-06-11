<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Import Threshold
    |--------------------------------------------------------------------------
    |
    | Imports with more rows than this threshold are queued to a background job.
    | Smaller imports are processed inline during the HTTP request.
    |
    */
    'queue_threshold' => (int) env('IMPORT_QUEUE_THRESHOLD', 500),
];
