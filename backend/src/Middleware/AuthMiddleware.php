<?php

namespace Innow\Middleware;

use Innow\Models\Session;

class AuthMiddleware {
    public static function check(): ?array {
        $token = $_COOKIE['innow_session'] ?? null;

        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            }
        }

        if (!$token) {
            return null;
        }

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        Session::deleteExpired();
        return Session::findByToken($token);
    }

    public static function handleOrRedirect(string $redirectTo = '/login'): array {
        $user = self::check();
        if (!$user) {
            header("Location: {$redirectTo}");
            exit();
        }
        return $user;
    }

    public static function guard(): array {
        $user = self::handleOrRedirect('/login');
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Robots-Tag: noindex, nofollow');
        return $user;
    }
}
