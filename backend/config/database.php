<?php

return [
    'driver'    => process_env('DB_DRIVER', 'mysql'),
    'host'      => process_env('DB_HOST', '127.0.0.1'),
    'port'      => process_env('DB_PORT', '3306'),
    'database'  => process_env('DB_NAME', 'innow_db'),
    'username'  => process_env('DB_USER', 'root'),
    'password'  => process_env('DB_PASS', ''),
    'charset'   => 'utf8mb4',
    'sqlite_path' => process_env('DB_SQLITE_PATH', '/tmp/innow.sqlite'),
];
