<?php

namespace Innow\Middleware;

class RateLimiter {
    private static string $storageDir;
    private static int $maxRequests = 60;
    private static int $windowSeconds = 60;

    public static function init(): void {
        $dir = sys_get_temp_dir() . '/innow_rate';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        self::$storageDir = $dir;
    }

    public static function check(string $key): bool {
        self::init();
        $file = self::$storageDir . '/' . hash('sha256', $key);
        $now = time();
        $data = ['count' => 0, 'reset' => $now + self::$windowSeconds];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
            if ($now > $data['reset']) {
                $data = ['count' => 0, 'reset' => $now + self::$windowSeconds];
            }
        }

        if ($data['count'] >= self::$maxRequests) {
            return false;
        }

        $data['count']++;
        file_put_contents($file, json_encode($data));
        return true;
    }

    public static function getMaxRequests(): int {
        return self::$maxRequests;
    }
}
