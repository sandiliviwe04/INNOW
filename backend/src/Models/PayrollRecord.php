<?php

namespace Innow\Models;

use Innow\Config\Database;

/**
 * Persisted snapshot of a generated payroll report for one employee
 * and one period. Unique per (user_id, period_type, period_start) —
 * re-generating the SAME period updates its one row instead of
 * creating duplicates; a different (later) period always gets its own row.
 */
class PayrollRecord {

    public static function upsert(array $data): array {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT id FROM payroll_records
            WHERE user_id = :user_id AND period_type = :period_type AND period_start = :period_start
        ");
        $stmt->execute([
            'user_id' => $data['user_id'],
            'period_type' => $data['period_type'],
            'period_start' => $data['period_start'],
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            $id = $existing['id'];
            $stmt = $db->prepare("
                UPDATE payroll_records SET
                    period_end = :period_end,
                    expected_hours = :expected_hours,
                    actual_hours = :actual_hours,
                    regular_hours = :regular_hours,
                    overtime_hours = :overtime_hours,
                    compensation_type = :compensation_type,
                    rate_used = :rate_used,
                    overtime_multiplier = :overtime_multiplier,
                    regular_pay = :regular_pay,
                    overtime_pay = :overtime_pay,
                    total_pay = :total_pay,
                    generated_at = NOW(),
                    generated_by = :generated_by
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $id,
                'period_end' => $data['period_end'],
                'expected_hours' => $data['expected_hours'],
                'actual_hours' => $data['actual_hours'],
                'regular_hours' => $data['regular_hours'],
                'overtime_hours' => $data['overtime_hours'],
                'compensation_type' => $data['compensation_type'],
                'rate_used' => $data['rate_used'],
                'overtime_multiplier' => $data['overtime_multiplier'],
                'regular_pay' => $data['regular_pay'],
                'overtime_pay' => $data['overtime_pay'],
                'total_pay' => $data['total_pay'],
                'generated_by' => $data['generated_by'] ?? null,
            ]);
        } else {
            $id = 'PAY-' . sprintf('%04d', rand(1000, 9999));
            $stmt = $db->prepare("
                INSERT INTO payroll_records
                    (id, user_id, period_type, period_start, period_end, expected_hours, actual_hours,
                     regular_hours, overtime_hours, compensation_type, rate_used, overtime_multiplier,
                     regular_pay, overtime_pay, total_pay, generated_by)
                VALUES
                    (:id, :user_id, :period_type, :period_start, :period_end, :expected_hours, :actual_hours,
                     :regular_hours, :overtime_hours, :compensation_type, :rate_used, :overtime_multiplier,
                     :regular_pay, :overtime_pay, :total_pay, :generated_by)
            ");
            $stmt->execute([
                'id' => $id,
                'user_id' => $data['user_id'],
                'period_type' => $data['period_type'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'expected_hours' => $data['expected_hours'],
                'actual_hours' => $data['actual_hours'],
                'regular_hours' => $data['regular_hours'],
                'overtime_hours' => $data['overtime_hours'],
                'compensation_type' => $data['compensation_type'],
                'rate_used' => $data['rate_used'],
                'overtime_multiplier' => $data['overtime_multiplier'],
                'regular_pay' => $data['regular_pay'],
                'overtime_pay' => $data['overtime_pay'],
                'total_pay' => $data['total_pay'],
                'generated_by' => $data['generated_by'] ?? null,
            ]);
        }

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.name as staff_name, u.department
            FROM payroll_records p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Previously generated reports for one employee, most recent period first. */
    public static function forUser(string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.name as staff_name, u.department
            FROM payroll_records p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id = :user_id
            ORDER BY p.period_start DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function delete(string $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM payroll_records WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}