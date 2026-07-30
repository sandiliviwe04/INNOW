<?php

namespace Innow\Models;

use Innow\Config\Database;

class LeaveRequest {
    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'LEAVE-' . sprintf('%04d', rand(1000, 9999));

        $stmt = $db->prepare("
            INSERT INTO leave_requests (id, user_id, leave_type, start_date, end_date, days_requested, reason, status)
            VALUES (:id, :user_id, :leave_type, :start_date, :end_date, :days_requested, :reason, 'PENDING')
        ");

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days_requested' => $data['days_requested'],
            'reason' => $data['reason'] ?? '',
        ]);

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT l.*, u.name as user_name, u.department
            FROM leave_requests l
            JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT l.*, u.name as user_name, u.department, u.email,
                   r.name as reviewed_by_name
            FROM leave_requests l
            JOIN users u ON l.user_id = u.id
            LEFT JOIN users r ON l.reviewed_by = r.id
            ORDER BY l.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function forUser(string $userId, bool $isAdmin = false): array {
        $db = Database::getConnection();
        if ($isAdmin) {
            $stmt = $db->query("
                SELECT l.*, u.name as user_name, u.department, u.email,
                       r.name as reviewed_by_name
                FROM leave_requests l
                JOIN users u ON l.user_id = u.id
                LEFT JOIN users r ON l.reviewed_by = r.id
                ORDER BY l.created_at DESC
            ");
        } else {
            $stmt = $db->prepare("
                SELECT l.*, u.name as user_name, u.department, u.email,
                       r.name as reviewed_by_name
                FROM leave_requests l
                JOIN users u ON l.user_id = u.id
                LEFT JOIN users r ON l.reviewed_by = r.id
                WHERE l.user_id = :user_id
                ORDER BY l.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        }
        return $stmt->fetchAll();
    }

    public static function pending(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT l.*, u.name as user_name, u.department, u.email,
                   r.name as reviewed_by_name
            FROM leave_requests l
            JOIN users u ON l.user_id = u.id
            LEFT JOIN users r ON l.reviewed_by = r.id
            WHERE l.status = 'PENDING'
            ORDER BY l.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public static function updateStatus(string $id, string $status, ?string $reviewedBy = null): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE leave_requests 
            SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    public static function delete(string $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM leave_requests WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
