<?php

namespace Innow\Models;

use Innow\Config\Database;
use PDO;

class Session {
    public static function create(string $userId): string {
        $db = Database::getConnection();
        $id = 'SES-' . bin2hex(random_bytes(16));
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60)); // 30 minutes

        try {
            $stmt = $db->prepare("
                INSERT INTO sessions (id, user_id, token, created_at, expires_at, last_activity_at)
                VALUES (:id, :user_id, :token, NOW(), :expires_at, NOW())
            ");
            $stmt->execute([
                'id' => $id,
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]);
        } catch (\Throwable $e) {
            $stmt = $db->prepare("
                INSERT INTO sessions (id, user_id, token, created_at, expires_at)
                VALUES (:id, :user_id, :token, NOW(), :expires_at)
            ");
            $stmt->execute([
                'id' => $id,
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]);
        }

        return $token;
    }

    public static function findByToken(string $token): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.*, u.name, u.email, u.role, u.department, u.status
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.token = :token AND s.expires_at > NOW()
        ");
        $stmt->execute(['token' => $token]);
        $session = $stmt->fetch();
        if ($session) {
            self::updateActivity($token);
        }
        return $session ?: null;
    }

    public static function updateActivity(string $token): void
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("UPDATE sessions SET last_activity_at = NOW() WHERE token = :token");
            $stmt->execute(['token' => $token]);
        } catch (\Throwable $e) {
            // Column may not exist on older schemas; safe to ignore
        }
    }

    public static function delete(string $token): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM sessions WHERE token = :token");
        return $stmt->execute(['token' => $token]);
    }

    public static function deleteExpired(): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM sessions WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
