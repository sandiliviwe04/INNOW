<?php

namespace Innow\Middleware;

class CsrfMiddleware {
    private static string $storageDir;

    public static function init(): void {
        $dir = sys_get_temp_dir() . '/innow_csrf';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        self::$storageDir = $dir;
    }

    public static function generateToken(string $sessionToken): string {
        self::init();
        $file = self::$storageDir . '/' . hash('sha256', $sessionToken);

        if (file_exists($file)) {
            $existing = json_decode(file_get_contents($file), true);
            if ($existing && time() < $existing['expires']) {
                return $existing['token'];
            }
        }

        $token = bin2hex(random_bytes(32));
        $data = [
            'token' => $token,
            'expires' => time() + 86400
        ];
        file_put_contents($file, json_encode($data));
        return $token;
    }

    public static function validateToken(string $sessionToken, string $clientToken): bool {
        self::init();
        $file = self::$storageDir . '/' . hash('sha256', $sessionToken);
        if (!file_exists($file)) {
            return false;
        }
        $data = json_decode(file_get_contents($file), true);
        if (!$data || time() > $data['expires']) {
            @unlink($file);
            return false;
        }
        $valid = hash_equals($data['token'], $clientToken);
        if ($valid) {
            $data['expires'] = time() + 86400;
            file_put_contents($file, json_encode($data));
        }
        return $valid;
    }

    public static function invalidateToken(string $sessionToken): void {
        self::init();
        $file = self::$storageDir . '/' . hash('sha256', $sessionToken);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public static function cleanupExpired(): void {
        self::init();
        if (!is_dir(self::$storageDir)) {
            return;
        }
        $now = time();
        $files = glob(self::$storageDir . '/*');
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $data = json_decode(@file_get_contents($file), true);
            if (!$data || ($data['expires'] ?? 0) < $now) {
                @unlink($file);
            }
        }
    }
}
