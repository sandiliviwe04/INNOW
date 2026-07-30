<?php

namespace Innow\Controllers;

use Innow\Models\User;
use Innow\Models\Session;
use Innow\Utils\ResponseHelper;
use Innow\Utils\Validator;
use Innow\Middleware\RateLimiter;
use Innow\Middleware\CsrfMiddleware;

class AuthController {
    private static function verifyAndMaybeMigratePin(string $plainPin, array $user): bool {
        if (password_verify($plainPin, $user['pin'])) {
            return true;
        }

        if ($user['pin'] === $plainPin) {
            self::upgradePinHash($user['id'], $plainPin);
            return true;
        }

        return false;
    }

    private static function upgradePinHash(string $userId, string $plainPin): void {
        $db = \Innow\Config\Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pin = :pin WHERE id = :id");
        $stmt->execute([
            'pin' => password_hash($plainPin, PASSWORD_BCRYPT),
            'id' => $userId
        ]);
    }

    public function login(): void {
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $isApi = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
        if ($isApi && !RateLimiter::check('login:' . $clientIp)) {
            ResponseHelper::error('Too many login attempts. Please wait a minute.', 429);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $pin = trim($input['pin'] ?? '');
        $email = trim($input['email'] ?? '');

        $user = null;

        if ($email !== '' && $pin !== '') {
            $user = User::findByEmail($email);
            if ($user && self::verifyAndMaybeMigratePin($pin, $user)) {
                // authenticated
            } else {
                $user = null;
            }
        } else if ($email !== '') {
            $user = User::findByEmail($email);
        } else if ($pin !== '') {
            $users = User::all();
            foreach ($users as $u) {
                if (self::verifyAndMaybeMigratePin($pin, $u)) {
                    $user = $u;
                    break;
                }
            }
        }

        if (!$user) {
            ResponseHelper::error('Invalid PIN or Email address.', 401);
            return;
        }

        $token = Session::create($user['id']);

        // Set 7-day auth cookie
        setcookie('innow_session', $token, [
            'expires' => time() + (30 * 60),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        $csrfToken = CsrfMiddleware::generateToken($token);

        ResponseHelper::success([
            'token' => $token,
            'csrf_token' => $csrfToken,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'department' => $user['department'],
                'status' => $user['status'],
            ]
        ], 'Login successful.');
    }

    public function logout(): void {
        $token = $_COOKIE['innow_session'] ?? null;
        if ($token) {
            CsrfMiddleware::invalidateToken($token);
            Session::delete($token);
        }

        setcookie('innow_session', '', [
            'expires' => time() - 3600,
            'path' => '/'
        ]);

        header('Location: /login');
        exit;
    }
}
