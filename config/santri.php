<?php

return [
    'invoice' => [
        'period_year_min' => 2000,
        'period_year_future_limit' => env('SANTRI_INVOICE_PERIOD_YEAR_FUTURE_LIMIT', 5),
    ],

    'photo' => [
        'directory' => 'santri-photos',
        'min_width' => 200,
        'min_height' => 200,
        'max_width' => 2000,
        'max_height' => 2000,
        'max_size_kb' => 2048,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
];
