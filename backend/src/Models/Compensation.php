<?php

namespace Innow\Models;

use Innow\Config\Database;

/**
 * Admin-configured pay rate for one employee (hourly or monthly).
 * Insert-only, versioned by effective_date — same rationale as WorkSchedule.
 */
class Compensation {

    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'COMP-' . sprintf('%04d', rand(1000, 9999));

        $stmt = $db->prepare("
            INSERT INTO employee_compensation
                (id, user_id, compensation_type, hourly_rate, monthly_salary, overtime_multiplier,
                 currency, effective_date, created_by)
            VALUES
                (:id, :user_id, :compensation_type, :hourly_rate, :monthly_salary, :overtime_multiplier,
                 :currency, :effective_date, :created_by)
        ");

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'compensation_type' => $data['compensation_type'],
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'monthly_salary' => $data['monthly_salary'] ?? null,
            'overtime_multiplier' => $data['overtime_multiplier'] ?? 1.5,
            'currency' => $data['currency'] ?? 'ZAR',
            'effective_date' => $data['effective_date'],
            'created_by' => $data['created_by'] ?? null,
        ]);

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM employee_compensation WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allForUser(string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM employee_compensation
            WHERE user_id = :user_id
            ORDER BY effective_date DESC, created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function asOfDate(string $userId, string $date): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM employee_compensation
            WHERE user_id = :user_id AND effective_date <= :date
            ORDER BY effective_date DESC, created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId, 'date' => $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function currentForUser(string $userId): ?array {
        return self::asOfDate($userId, date('Y-m-d'));
    }
}