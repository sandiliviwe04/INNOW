<?php

namespace Innow\Models;

use Innow\Config\Database;
use PDO;

class AttendanceRecord {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT r.*, u.name as staff_name, u.department, u.role
            FROM attendance_records r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.timestamp DESC
        ");
        return $stmt->fetchAll();
    }

    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'LOG-' . sprintf('%04d', rand(1000, 9999));
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
        $synced = isset($data['synced_to_db']) ? (int)$data['synced_to_db'] : 1;

        $stmt = $db->prepare("
            INSERT INTO attendance_records (id, user_id, action, timestamp, method, notes)
            VALUES (:id, :user_id, :action, :timestamp, :method, :notes)
        ");

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'action' => $data['action'],
            'timestamp' => $timestamp,
            'method' => $data['method'] ?? 'BUTTON',
            'notes' => $data['notes'] ?? '',
        ]);

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, u.name as staff_name, u.department, u.role
            FROM attendance_records r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function getLatestForUser(string $userId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM attendance_records
            WHERE user_id = :user_id
            ORDER BY timestamp DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function markSynced(string $id): bool {
        $db = Database::getConnection();
        return true;
    }
}
