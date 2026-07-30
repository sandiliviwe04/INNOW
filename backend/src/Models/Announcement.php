<?php

namespace Innow\Models;

use Innow\Config\Database;

class Announcement {
    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'ANN-' . sprintf('%04d', rand(1000, 9999));

        $stmt = $db->prepare("
            INSERT INTO announcements (id, user_id, title, message)
            VALUES (:id, :user_id, :title, :message)
        ");

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'message' => $data['message'],
        ]);

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, u.name as user_name, u.department
            FROM announcements a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT a.*, u.name as user_name, u.department
            FROM announcements a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function delete(string $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
