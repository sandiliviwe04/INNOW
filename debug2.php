<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "<pre>";

echo "1. Loading .env...\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
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
    echo "   OK\n";
} else {
    echo "   .env NOT FOUND at $envFile\n";
}

echo "2. Loading Composer autoloader...\n";
$autoload = __DIR__ . '/backend/vendor/autoload.php';
if (!file_exists($autoload)) {
    die("   MISSING: $autoload\n");
}
require $autoload;
echo "   OK\n";

echo "3. Connecting to database...\n";
try {
    $db = \Innow\Config\Database::getConnection();
    echo "   OK\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n");
}

echo "4. Fetching User::all()...\n";
try {
    $allStaff = \Innow\Models\User::all();
    echo "   OK — " . count($allStaff) . " staff record(s)\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n");
}

echo "5. Fetching AttendanceRecord::all()...\n";
try {
    $recentLogs = \Innow\Models\AttendanceRecord::all();
    echo "   OK — " . count($recentLogs) . " record(s)\n";
} catch (\Throwable $e) {
    die("   FAILED: " . $e->getMessage() . "\n   in " . $e->getFile() . ":" . $e->getLine() . "\n");
}

echo "6. Checking dashboard view file exists...\n";
$viewPath = __DIR__ . '/frontend/views/dashboard/index.php';
if (!file_exists($viewPath)) {
    die("   MISSING: $viewPath\n");
}
echo "   OK\n";

echo "\nAll checks passed — the failure may be inside the view rendering itself.\n";
echo "Attempting to render it now (errors below, if any, are the real cause):\n";
echo "</pre><hr>";

$user = ['user_id' => 'TEST', 'name' => 'Diagnostic', 'role' => 'System Administrator', 'token' => 'test'];
$csrfToken = 'test-token';

try {
    require $viewPath;
} catch (\Throwable $e) {
    echo "<pre>RENDER FAILED: " . htmlspecialchars($e->getMessage()) . "\nin " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</pre>";
}