<?php

namespace Innow\Services;

use Innow\Config\Database;
use Innow\Models\WorkSchedule;
use Innow\Models\Compensation;
use Innow\Models\PayrollRecord;

class PayrollService {

    public function __construct() {
        $config = require __DIR__ . '/../../config/app.php';
        // Attendance timestamps are stored in local/server time — make sure
        // day-boundary grouping for payroll uses the same zone as the rest
        // of the app is configured for.
        @date_default_timezone_set($config['timezone'] ?? 'UTC');
    }

    /** Resolves [periodStart, periodEnd] (inclusive, 'Y-m-d') for a WEEK/MONTH/YEAR selection. */
    public function resolvePeriodRange(string $periodType, int $year, ?int $month = null, ?int $week = null): array {
        $periodType = strtoupper($periodType);

        if ($periodType === 'WEEK') {
            $week = $week ?: 1;
            $dto = new \DateTime();
            $dto->setISODate($year, $week, 1); // Monday of that ISO week
            $start = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $end = $dto->format('Y-m-d');
            return [$start, $end];
        }

        if ($periodType === 'MONTH') {
            $month = $month ?: 1;
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end = date('Y-m-t', strtotime($start));
            return [$start, $end];
        }

        // YEAR
        return [sprintf('%04d-01-01', $year), sprintf('%04d-12-31', $year)];
    }

    private function getAttendanceInRange(string $userId, string $start, string $end): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT action, timestamp
            FROM attendance_records
            WHERE user_id = :user_id
              AND timestamp >= :start
              AND timestamp < :end_exclusive
            ORDER BY timestamp ASC
        ");
        $stmt->execute([
            'user_id' => $userId,
            'start' => $start . ' 00:00:00',
            'end_exclusive' => date('Y-m-d', strtotime($end . ' +1 day')) . ' 00:00:00',
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Computes and persists the payroll snapshot for one employee/period.
     * Schedule & compensation are resolved AS OF $periodEnd (a fixed past
     * date), so re-running this later for a closed period reproduces the
     * same numbers even if the employee's CURRENT hours/salary changed.
     */
    public function generateForPeriod(string $userId, string $periodType, string $periodStart, string $periodEnd, ?string $generatedBy = null): array {
        $schedule = WorkSchedule::asOfDate($userId, $periodEnd);
        $compensation = Compensation::asOfDate($userId, $periodEnd);

        $records = $this->getAttendanceInRange($userId, $periodStart, $periodEnd);
        $daily = PayrollCalculator::buildDailyBreakdown($records, $schedule, $periodStart, $periodEnd);
        $hours = PayrollCalculator::splitRegularOvertime($daily, $schedule);
        $pay = PayrollCalculator::calculatePay($hours, $schedule, $compensation, strtoupper($periodType));

        $saved = PayrollRecord::upsert([
            'user_id' => $userId,
            'period_type' => strtoupper($periodType),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'expected_hours' => $hours['expected_hours'],
            'actual_hours' => $hours['actual_hours'],
            'regular_hours' => $hours['regular_hours'],
            'overtime_hours' => $hours['overtime_hours'],
            'compensation_type' => $pay['compensation_type'] ?? ($compensation['compensation_type'] ?? null),
            'rate_used' => $pay['rate_used'],
            'overtime_multiplier' => $pay['overtime_multiplier'],
            'regular_pay' => $pay['regular_pay'],
            'overtime_pay' => $pay['overtime_pay'],
            'total_pay' => $pay['total_pay'],
            'generated_by' => $generatedBy,
        ]);

        $saved['schedule_used'] = $schedule;
        $saved['compensation_used'] = $compensation;
        $saved['daily_breakdown'] = $daily;

        return $saved;
    }
}