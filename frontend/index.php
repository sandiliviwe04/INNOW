<?php

// Front Controller Entry Point
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load .env file into environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
        if (!array_key_exists($key, $_SERVER)) {
            $_SERVER[$key] = $value;
        }
        putenv($key . '=' . $value);
    }
}

$backendEnvFile = __DIR__ . '/../backend/.env';
if (file_exists($backendEnvFile)) {
    $lines = file($backendEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
        if (!array_key_exists($key, $_SERVER)) {
            $_SERVER[$key] = $value;
        }
        putenv($key . '=' . $value);
    }
}

// Helpers for process_env
if (!function_exists('process_env')) {
    function process_env(string $key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

// Custom PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Innow\\';
    $baseDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/backend/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use Innow\Router;
use Innow\Config\Database;
use Innow\Models\User;
use Innow\Models\AttendanceRecord;
use Innow\Middleware\CsrfMiddleware;

// Initialize MySQL Database
try {
    Database::getConnection();
} catch (\Throwable $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Current User Session
$user = \Innow\Middleware\AuthMiddleware::check();
$csrfToken = '';
if ($user) {
    $csrfToken = CsrfMiddleware::generateToken($user['token'] ?? '');
}

// Periodic cleanup of expired sessions and CSRF tokens
if (random_int(1, 100) === 1) {
    \Innow\Models\Session::deleteExpired();
    CsrfMiddleware::cleanupExpired();
}

$router = new Router();

// HTML Views Routes
$dashboardHandler = function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    $allStaff = User::all();
    $recentLogs = AttendanceRecord::all();
    require __DIR__ . '/views/dashboard/index.php';
};

$router->get('/', function() use ($user) {
    if ($user) {
        header('Location: /dashboard');
        exit;
    }

    $db = \Innow\Config\Database::getConnection();
    $userCount = $db->query('SELECT COUNT(*) as cnt FROM users')->fetch()['cnt'];

    if ($userCount === 0) {
        header('Location: /setup');
    } else {
        header('Location: /login');
    }
    exit;
});
$router->get('/dashboard', $dashboardHandler);

$router->get('/login', function() use ($user) {
    if ($user) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/views/auth/login.php';
});

$router->get('/setup', function() use ($user) {
    if ($user) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/views/setup.php';
});

$router->post('/setup', function() {
    require __DIR__ . '/views/setup.php';
});

$router->get('/logout', [\Innow\Controllers\AuthController::class, 'logout']);

$router->get('/checkin', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    $isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
    $allStaff = $isAdmin ? User::all() : [$user];
    require __DIR__ . '/views/attendance/checkin.php';
});

$router->get('/checkin/qr', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    $allStaff = User::all();
    require __DIR__ . '/views/attendance/qr_checkin.php';
});

$router->get('/staff', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    $allStaff = User::all();
    require __DIR__ . '/views/staff/directory.php';
});

$router->get('/logs', function() use ($user) {
    if (!$user) {
        header('Location: /login');
        exit;
    }
    $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
    if (!$isAdmin) {
        header('Location: /dashboard');
        exit;
    }
    header('Cache-Control: no-cache, no-store, must-revalidate, private');
    header('Pragma: no-cache');
    header('Expires: 0');
    $allStaff = User::all();
    $allLogs = AttendanceRecord::all();
    require __DIR__ . '/views/attendance/logs.php';
});

$router->get('/docs', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    require __DIR__ . '/views/docs/viewer.php';
});

$router->get('/leave', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    require __DIR__ . '/views/leave/index.php';
});

$router->get('/announcements', function() use ($user, $csrfToken) {
    \Innow\Middleware\AuthMiddleware::guard();
    require __DIR__ . '/views/announcements/index.php';
});

// JSON API Routes
$router->post('/api/login', [\Innow\Controllers\AuthController::class, 'login']);
$router->get('/api/checkin/qr-payload', [\Innow\Controllers\AttendanceController::class, 'getQRPayload']);
$router->post('/api/checkin/qr', [\Innow\Controllers\AttendanceController::class, 'checkinQR']);
$router->post('/api/checkin/button', [\Innow\Controllers\AttendanceController::class, 'checkinButton']);
$router->post('/api/manual-entry', [\Innow\Controllers\AttendanceController::class, 'manualEntry']);
$router->get('/api/dashboard/summary', [\Innow\Controllers\DashboardController::class, 'getSummary']);
$router->get('/api/staff', [\Innow\Controllers\UserController::class, 'index']);
$router->post('/api/staff/add', [\Innow\Controllers\UserController::class, 'store']);
$router->post('/api/staff/remove', [\Innow\Controllers\UserController::class, 'destroy']);
$router->post('/api/staff/reset-pin', [\Innow\Controllers\UserController::class, 'resetPin']);
$router->get('/api/leaves', [\Innow\Controllers\LeaveController::class, 'index']);
$router->post('/api/leaves', [\Innow\Controllers\LeaveController::class, 'store']);
$router->post('/api/leaves/update', [\Innow\Controllers\LeaveController::class, 'update']);
$router->post('/api/leaves/delete', [\Innow\Controllers\LeaveController::class, 'destroy']);
$router->get('/api/announcements', [\Innow\Controllers\AnnouncementController::class, 'index']);
$router->post('/api/announcements', [\Innow\Controllers\AnnouncementController::class, 'store']);
$router->post('/api/announcements/delete', [\Innow\Controllers\AnnouncementController::class, 'destroy']);

// Dispatch Request using original requested URI
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['HTTP_X_ORIGINAL_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

try {
    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}