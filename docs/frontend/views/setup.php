<?php

// Show errors during setup
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Standalone setup page - does NOT include index.php to avoid infinite recursion

// Load .env
$envFile = __DIR__ . '/../../.env';
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

// Load backend .env
$backendEnvFile = __DIR__ . '/../../backend/.env';
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

// Load Composer autoloader
$autoloadPath = __DIR__ . '/../../backend/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    die("<h1>Setup Error</h1><p>Composer dependencies are missing.</p><p>Run this command in the <code>backend</code> folder first:</p><pre>composer install --no-dev --optimize-autoloader</pre>");
}
require $autoloadPath;

// Connect to database directly
use Innow\Config\Database;
use Innow\Models\User;

try {
    $db = Database::getConnection();
    $userCount = $db->query('SELECT COUNT(*) as cnt FROM users')->fetch()['cnt'];
} catch (Exception $e) {
    die("<h1>Database Connection Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p><p>Check your .env credentials and ensure MySQL is running.</p>");
}

if ($userCount > 0) {
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    $role = trim($_POST['role'] ?? 'System Administrator');
    $department = trim($_POST['department'] ?? 'Operations');

    $errors = [];
    if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!preg_match('/^\d{4}$/', $pin)) $errors[] = 'PIN must be exactly 4 digits.';

    $existing = User::findByEmail($email);
    if ($existing) $errors[] = 'Email already exists.';

    if (empty($errors)) {
        try {
            $user = User::create(compact('name', 'email', 'pin', 'role', 'department'));
            if ($user) {
                header('Location: /login?setup=success');
                exit;
            }
            $errors[] = 'Failed to create user.';
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INNOW — Initial Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full bg-zinc-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl border border-zinc-200 shadow-xl space-y-6">
        <div class="text-center">
            <div class="mx-auto w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-md mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900">INNOW Setup</h1>
            <p class="text-xs text-zinc-500 mt-1">Create the first admin account to get started.</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-xs text-red-800 space-y-1">
            <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Thabo Mokoena" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="thabo.m@innow.com" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">4-Digit PIN</label>
                <input type="text" name="pin" maxlength="4" required placeholder="1234" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-center font-mono text-lg tracking-widest">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        <option>System Administrator</option>
                        <option>Operations Manager</option>
                        <option>Staff Member</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">Department</label>
                    <input type="text" name="department" value="Operations" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>
            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Create Admin Account
            </button>
        </form>

        <p class="text-[10px] text-zinc-400 text-center">This page only appears when no users exist. After setup, it redirects to login.</p>
    </div>
</body>
</html>
