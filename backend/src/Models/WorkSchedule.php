<?php

namespace Innow\Models;

use Innow\Config\Database;

/**
 * Admin-configured working-hours schedule for one employee.
 * Rows are insert-only (versioned by effective_date) — there is no
 * update/delete, so historical payroll calculations that resolved a
 * past schedule version never change under them.
 */
class WorkSchedule {

    public static function create(array $data): array {
        $db = Database::getConnection();
        $id = 'SCH-' . sprintf('%04d', rand(1000, 9999));

        $stmt = $db->prepare("
            INSERT INTO employee_work_schedules
                (id, user_id, hours_per_day, hours_per_week, working_days, start_time, end_time,
                 break_duration_minutes, break_paid, effective_date, created_by)
            VALUES
                (:id, :user_id, :hours_per_day, :hours_per_week, :working_days, :start_time, :end_time,
                 :break_duration_minutes, :break_paid, :effective_date, :created_by)
        ");

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'hours_per_day' => $data['hours_per_day'],
            'hours_per_week' => $data['hours_per_week'],
            'working_days' => $data['working_days'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'break_duration_minutes' => $data['break_duration_minutes'] ?? 0,
            'break_paid' => !empty($data['break_paid']) ? 1 : 0,
            'effective_date' => $data['effective_date'],
            'created_by' => $data['created_by'] ?? null,
        ]);

        return self::find($id);
    }

    public static function find(string $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM employee_work_schedules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Full version history for one employee, newest first. */
    public static function allForUser(string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM employee_work_schedules
            WHERE user_id = :user_id
            ORDER BY effective_date DESC, created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /** The schedule version that was in effect on a given date, or null if none configured yet. */
    public static function asOfDate(string $userId, string $date): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM employee_work_schedules
            WHERE user_id = :user_id AND effective_date <= :date
            ORDER BY effective_date DESC, created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId, 'date' => $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** The schedule in effect today — used to prefill the admin config screen. */
    public static function currentForUser(string $userId): ?array {
        return self::asOfDate($userId, date('Y-m-d'));
    }
}