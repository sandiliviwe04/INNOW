<?php

return [
    'name' => 'INNOW — Digital Attendance Tracking System',
    'url' => process_env('APP_URL', 'http://localhost:3000'),
    'env' => process_env('APP_ENV', 'production'),
    'secret' => process_env('APP_SECRET', 'lc_studio_innow_secret_key_2026'),
    'timezone' => 'Africa/Johannesburg',
    'qr_validity_seconds' => 30,
];
