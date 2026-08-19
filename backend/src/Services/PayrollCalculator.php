<?php

namespace Innow\Services;

/**
 * Pure calculation engine for working hours, overtime and pay.
 * No database access here — everything is driven by the arrays passed
 * in, so it is 100% unit-testable and NOTHING is hard-coded (no 8h/day,
 * no 40h/week — those always come from the $schedule array).
 */
class PayrollCalculator {

    /** "1,2,3,4,5" -> [1,2,3,4,5]  (ISO-8601 weekday numbers, Mon=1..Sun=7) */
    public static function parseWorkingDays(string $csv): array {
        if (trim($csv) === '') {
            return [];
        }
        return array_values(array_filter(
            array_map(fn($p) => (int) trim($p), explode(',', $csv)),
            fn($n) => $n >= 1 && $n <= 7
        ));
    }

    public static function isWorkingDay(?array $schedule, string $date): bool {
        if (!$schedule) {
            return false;
        }
        $weekday = (int) date('N', strtotime($date)); // 1=Mon..7=Sun
        return in_array($weekday, self::parseWorkingDays($schedule['working_days'] ?? ''), true);
    }

    /** Groups a flat, timestamp-sorted attendance list by calendar date ('Y-m-d'). */
    public static function groupRecordsByDate(array $records): array {
        $byDate = [];
        foreach ($records as $r) {
            $date = substr($r['timestamp'], 0, 10);
            $byDate[$date][] = $r;
        }
        foreach ($byDate as $date => $items) {
            usort($items, fn($a, $b) => strcmp($a['timestamp'], $b['timestamp']));
            $byDate[$date] = $items;
        }
        return $byDate;
    }

    /**
     * Walks one day's CLOCK_IN / CLOCK_OUT / BREAK_START / BREAK_END events
     * (each ['action' => ..., 'timestamp' => 'Y-m-d H:i:s']) and returns the
     * gross clocked seconds plus the break seconds actually recorded.
     * An open CLOCK_IN with no matching CLOCK_OUT that day is NOT counted —
     * we never guess an end time.
     */
    public static function computeDaySpans(array $dayRecords): array {
        $grossSeconds = 0;
        $breakSeconds = 0;
        $clockInAt = null;
        $breakStartAt = null;

        foreach ($dayRecords as $r) {
            $ts = strtotime($r['timestamp']);
            switch ($r['action']) {
                case 'CLOCK_IN':
                    $clockInAt = $ts;
                    break;
                case 'CLOCK_OUT':
                    if ($clockInAt !== null) {
                        $grossSeconds += max(0, $ts - $clockInAt);
                        $clockInAt = null;
                    }
                    $breakStartAt = null;
                    break;
                case 'BREAK_START':
                    if ($clockInAt !== null) {
                        $breakStartAt = $ts;
                    }
                    break;
                case 'BREAK_END':
                    if ($breakStartAt !== null) {
                        $breakSeconds += max(0, $ts - $breakStartAt);
                        $breakStartAt = null;
                    }
                    break;
            }
        }

        return ['gross_seconds' => $grossSeconds, 'break_seconds' => $breakSeconds];
    }

    /**
     * Actual worked hours for ONE calendar day.
     * Break rule:
     *  - break_paid = true  -> breaks are NOT deducted (already inside the gross span)
     *  - break_paid = false -> deduct the break time actually recorded that day;
     *                          if no BREAK_START/BREAK_END events exist but time
     *                          was worked, fall back to the configured
     *                          break_duration_minutes once (flat unpaid-lunch rule).
     */
    public static function computeDailyHours(string $date, array $dayRecords, ?array $schedule): array {
        $spans = self::computeDaySpans($dayRecords);
        $grossSeconds = $spans['gross_seconds'];
        $breakSeconds = $spans['break_seconds'];

        $isWorkingDay = self::isWorkingDay($schedule, $date);
        $expectedHours = ($isWorkingDay && $schedule) ? (float) $schedule['hours_per_day'] : 0.0;

        $deductionSeconds = 0;
        if ($grossSeconds > 0 && $schedule && empty($schedule['break_paid'])) {
            $deductionSeconds = $breakSeconds > 0
                ? $breakSeconds
                : ((int) ($schedule['break_duration_minutes'] ?? 0)) * 60;
        }

        $netSeconds = max(0, $grossSeconds - $deductionSeconds);

        return [
            'date' => $date,
            'is_working_day' => $isWorkingDay,
            'expected_hours' => round($expectedHours, 2),
            'actual_hours' => round($netSeconds / 3600, 2),
        ];
    }

    /** Per-day breakdown for every calendar day in [start, end] inclusive. */
    public static function buildDailyBreakdown(array $records, ?array $schedule, string $periodStart, string $periodEnd): array {
        $byDate = self::groupRecordsByDate($records);
        $days = [];

        $cursor = strtotime($periodStart);
        $end = strtotime($periodEnd);
        while ($cursor <= $end) {
            $date = date('Y-m-d', $cursor);
            $days[] = self::computeDailyHours($date, $byDate[$date] ?? [], $schedule);
            $cursor = strtotime('+1 day', $cursor);
        }

        return $days;
    }

    /**
     * Splits actual hours into regular vs overtime:
     *   1. DAILY cap = schedule.hours_per_day on scheduled working days;
     *      hours worked on a non-working day count entirely as overtime.
     *   2. WEEKLY cap = schedule.hours_per_week, applied per ISO week —
     *      any regular hours above the weekly cap are reclassified as overtime.
     * Returns period totals only (both configured caps are honoured — neither
     * 8h/day nor 40h/week is ever assumed).
     */
    public static function splitRegularOvertime(array $dailyBreakdown, ?array $schedule): array {
        $hoursPerWeek = $schedule ? (float) $schedule['hours_per_week'] : 0.0;

        $weeks = [];
        foreach ($dailyBreakdown as $day) {
            $weekKey = date('o-\WW', strtotime($day['date']));
            $weeks[$weekKey][] = $day;
        }

        $totalExpected = 0.0;
        $totalActual = 0.0;
        $totalRegular = 0.0;
        $totalOvertime = 0.0;

        foreach ($weeks as $weekDays) {
            $weekRegular = 0.0;
            $weekOvertime = 0.0;

            foreach ($weekDays as $day) {
                $totalExpected += $day['expected_hours'];
                $totalActual += $day['actual_hours'];

                if ($day['is_working_day']) {
                    $dayRegular = min($day['actual_hours'], $day['expected_hours']);
                    $dayOvertime = max(0.0, $day['actual_hours'] - $day['expected_hours']);
                } else {
                    $dayRegular = 0.0;
                    $dayOvertime = $day['actual_hours'];
                }

                $weekRegular += $dayRegular;
                $weekOvertime += $dayOvertime;
            }

            if ($hoursPerWeek > 0 && $weekRegular > $hoursPerWeek) {
                $excess = $weekRegular - $hoursPerWeek;
                $weekRegular = $hoursPerWeek;
                $weekOvertime += $excess;
            }

            $totalRegular += $weekRegular;
            $totalOvertime += $weekOvertime;
        }

        return [
            'expected_hours' => round($totalExpected, 2),
            'actual_hours' => round($totalActual, 2),
            'regular_hours' => round($totalRegular, 2),
            'overtime_hours' => round($totalOvertime, 2),
        ];
    }

    /**
     * Turns an hours breakdown into pay.
     * HOURLY:  regular = regular_hours * rate ; overtime = overtime_hours * rate * multiplier
     * MONTHLY: prorated against an "implied hourly rate" derived from
     *          hours_per_week (never a hard-coded 40h week), so overtime
     *          pay for salaried staff is still meaningful.
     */
    public static function calculatePay(array $hours, ?array $schedule, ?array $compensation, string $periodType): array {
        if (!$compensation) {
            return [
                'compensation_type' => null,
                'rate_used' => 0.0,
                'overtime_multiplier' => 0.0,
                'regular_pay' => 0.0,
                'overtime_pay' => 0.0,
                'total_pay' => 0.0,
            ];
        }

        $type = $compensation['compensation_type'];
        $multiplier = (float) ($compensation['overtime_multiplier'] ?? 1.5);
        $regularHours = (float) $hours['regular_hours'];
        $overtimeHours = (float) $hours['overtime_hours'];
        $expectedHours = (float) $hours['expected_hours'];

        if ($type === 'HOURLY') {
            $rate = (float) ($compensation['hourly_rate'] ?? 0);
            $regularPay = $regularHours * $rate;
            $overtimePay = $overtimeHours * $rate * $multiplier;

            return [
                'compensation_type' => 'HOURLY',
                'rate_used' => round($rate, 2),
                'overtime_multiplier' => $multiplier,
                'regular_pay' => round($regularPay, 2),
                'overtime_pay' => round($overtimePay, 2),
                'total_pay' => round($regularPay + $overtimePay, 2),
            ];
        }

        // MONTHLY
        $monthlySalary = (float) ($compensation['monthly_salary'] ?? 0);
        $hoursPerWeek = $schedule ? (float) $schedule['hours_per_week'] : 0.0;
        $monthlyExpectedBaseline = $hoursPerWeek * (52 / 12); // avg weeks/month
        $impliedHourlyRate = $monthlyExpectedBaseline > 0 ? $monthlySalary / $monthlyExpectedBaseline : 0.0;

        switch ($periodType) {
            case 'WEEK':
                $baseline = $monthlySalary * 12 / 52;
                break;
            case 'YEAR':
                $baseline = $monthlySalary * 12;
                break;
            case 'MONTH':
            default:
                $baseline = $monthlySalary;
                break;
        }

        $regularPay = $expectedHours > 0
            ? $baseline * (min($regularHours, $expectedHours) / $expectedHours)
            : 0.0;

        $overtimePay = $overtimeHours * $impliedHourlyRate * $multiplier;

        return [
            'compensation_type' => 'MONTHLY',
            'rate_used' => round($monthlySalary, 2),
            'overtime_multiplier' => $multiplier,
            'regular_pay' => round($regularPay, 2),
            'overtime_pay' => round($overtimePay, 2),
            'total_pay' => round($regularPay + $overtimePay, 2),
        ];
    }
}