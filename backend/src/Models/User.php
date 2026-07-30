<?php

namespace Innow\Models;

use Innow\Config\Database;
use PDO;

class User {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email)");
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByPin(string $pin): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE pin = :pin");
        $stmt->execute(['pin' => trim($pin)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'STF-' . sprintf('%04d', rand(1007, 9999));
        $qrCode = 'INNOW-QR-' . str_replace('STF-', '', $id) . '-' . strtoupper(explode(' ', $data['name'])[0]);

        $stmt = $db->prepare("
            INSERT INTO users (id, name, email, pin, role, department, phone, emergency_contact, status, qr_code)
            VALUES (:id, :name, :email, :pin, :role, :department, :phone, :emergency_contact, 'OFFSITE', :qr_code)
        ");

        $hashedPin = password_hash($data['pin'], PASSWORD_BCRYPT);

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'pin' => $hashedPin,
            'role' => $data['role'] ?? 'Staff Member',
            'department' => $data['department'] ?? 'Software Engineering',
            'phone' => $data['phone'] ?? '+27 82 000 0000',
            'emergency_contact' => $data['emergency_contact'] ?? '',
            'qr_code' => $qrCode,
        ]);

        return self::find($id);
    }

    public static function updateStatus(string $id, string $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function getOnsite(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM users WHERE status IN ('ONSITE', 'BREAK') ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function delete(string $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
