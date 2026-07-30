<?php

require_once __DIR__ . '/../src/Config/Database.php';

use Innow\Config\Database;

if (!function_exists('process_env')) {
    function process_env(string $key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) $value = substr($value, 1, -1);
        if (!array_key_exists($key, $_ENV)) $_ENV[$key] = $value;
        if (!array_key_exists($key, $_SERVER)) $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

$backendEnvFile = __DIR__ . '/../backend/.env';
if (file_exists($backendEnvFile)) {
    $lines = file($backendEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) $value = substr($value, 1, -1);
        if (!array_key_exists($key, $_ENV)) $_ENV[$key] = $value;
        if (!array_key_exists($key, $_SERVER)) $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

Database::getConnection();
