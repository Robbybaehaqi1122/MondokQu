<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'queue' => env('BACKUP_QUEUE', 'default'),
];
