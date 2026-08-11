<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "<pre>";

$envFile = __DIR__ . '/.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $key = trim(substr($line, 0, $pos));
    $value = trim(substr($line, $pos + 1));
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
}

require __DIR__ . '/backend/vendor/autoload.php';

if (!function_exists('process_env')) {
    function process_env(string $key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

echo "1. Cookie present: " . (isset($_COOKIE['innow_session']) ? 'YES (' . substr($_COOKIE['innow_session'], 0, 12) . '...)' : 'NO') . "\n\n";

echo "2. Calling AuthMiddleware::check()...\n";
try {
    $user = \Innow\Middleware\AuthMiddleware::check();
    echo "   OK — user: " . ($user ? json_encode(['id' => $user['user_id'] ?? null, 'name' => $user['name'] ?? null, 'role' => $user['role'] ?? null]) : 'null (not logged in)') . "\n\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n");
}

if (!$user) {
    die("Not logged in in this request — log in first, then reload this page while still logged in.\n");
}

echo "3. Calling CsrfMiddleware::generateToken()...\n";
try {
    $csrfToken = \Innow\Middleware\CsrfMiddleware::generateToken($user['token'] ?? '');
    echo "   OK — token: " . substr($csrfToken, 0, 12) . "...\n\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n");
}

echo "4. Calling AuthMiddleware::guard()...\n";
try {
    \Innow\Middleware\AuthMiddleware::guard();
    echo "   OK\n\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n");
}

echo "5. Fetching User::all() and AttendanceRecord::all()...\n";
try {
    $allStaff = \Innow\Models\User::all();
    $recentLogs = \Innow\Models\AttendanceRecord::all();
    echo "   OK\n\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n");
}

echo "All real-flow checks passed. Rendering the actual dashboard view now:\n";
echo "</pre><hr>";

try {
    require __DIR__ . '/frontend/views/dashboard/index.php';
} catch (\Throwable $e) {
    echo "<pre>RENDER FAILED: " . htmlspecialchars($e->getMessage()) . "\nin " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}