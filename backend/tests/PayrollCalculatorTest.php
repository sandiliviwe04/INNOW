<?php

namespace Innow\Tests;

use Innow\Services\PayrollCalculator;
use PHPUnit\Framework\TestCase;

class PayrollCalculatorTest extends TestCase
{
    private function schedule(array $overrides = []): array
    {
        return array_merge([
            'hours_per_day' => 8,
            'hours_per_week' => 40,
            'working_days' => '1,2,3,4,5', // Mon-Fri
            'break_duration_minutes' => 60,
            'break_paid' => 0,
        ], $overrides);
    }

    public function testExpectedHoursComesFromScheduleConfigNotHardCoded(): void
    {
        // Deliberately NOT 8 — proves nothing is hard-coded.
        $schedule = $this->schedule(['hours_per_day' => 6]);
        $day = PayrollCalculator::computeDailyHours('2026-08-03', [], $schedule); // Monday, no records
        $this->assertEquals(6.0, $day['expected_hours']);
        $this->assertEquals(0.0, $day['actual_hours']);
    }

    public function testUnpaidBreakFallsBackToConfiguredDurationWhenNoBreakEventsLogged(): void
    {
        $schedule = $this->schedule(); // break_paid = false, break_duration_minutes = 60
        $records = [
            ['action' => 'CLOCK_IN', 'timestamp' => '2026-08-03 08:00:00'],
            ['action' => 'CLOCK_OUT', 'timestamp' => '2026-08-03 18:00:00'], // 10h gross
        ];
        $day = PayrollCalculator::computeDailyHours('2026-08-03', $records, $schedule);
        $this->assertEquals(9.0, $day['actual_hours']); // 10h - 1h configured unpaid break
        $this->assertEquals(8.0, $day['expected_hours']);
    }

    public function testActualRecordedBreakOverridesConfiguredFallbackWhenUnpaid(): void
    {
        $schedule = $this->schedule(); // break_duration_minutes = 60 (fallback)
        $records = [
            ['action' => 'CLOCK_IN', 'timestamp' => '2026-08-03 08:00:00'],
            ['action' => 'BREAK_START', 'timestamp' => '2026-08-03 12:00:00'],
            ['action' => 'BREAK_END', 'timestamp' => '2026-08-03 12:45:00'], // 45 min actual break
            ['action' => 'CLOCK_OUT', 'timestamp' => '2026-08-03 18:00:00'], // 10h gross
        ];
        $day = PayrollCalculator::computeDailyHours('2026-08-03', $records, $schedule);
        $this->assertEquals(9.25, $day['actual_hours']); // 10h - 45min ACTUAL break, not the 60min fallback
    }

    public function testPaidBreakIsNotDeductedAtAll(): void
    {
        $schedule = $this->schedule(['break_paid' => 1]);
        $records = [
            ['action' => 'CLOCK_IN', 'timestamp' => '2026-08-03 08:00:00'],
            ['action' => 'CLOCK_OUT', 'timestamp' => '2026-08-03 18:00:00'], // 10h gross
        ];
        $day = PayrollCalculator::computeDailyHours('2026-08-03', $records, $schedule);
        $this->assertEquals(10.0, $day['actual_hours']);
    }

    public function testDailyOvertimeMatchesSpecExample(): void
    {
        // Spec example: Expected = 8, Actual = 9, Overtime = 1
        $schedule = $this->schedule();
        $daily = [
            ['date' => '2026-08-03', 'is_working_day' => true, 'expected_hours' => 8.0, 'actual_hours' => 9.0],
        ];
        $result = PayrollCalculator::splitRegularOvertime($daily, $schedule);
        $this->assertEquals(8.0, $result['regular_hours']);
        $this->assertEquals(1.0, $result['overtime_hours']);
    }

    public function testHoursWorkedOnNonScheduledDayAreEntirelyOvertime(): void
    {
        $schedule = $this->schedule(); // Mon-Fri only
        $daily = [
            ['date' => '2026-08-08', 'is_working_day' => false, 'expected_hours' => 0.0, 'actual_hours' => 5.0], // Saturday
        ];
        $result = PayrollCalculator::splitRegularOvertime($daily, $schedule);
        $this->assertEquals(0.0, $result['regular_hours']);
        $this->assertEquals(5.0, $result['overtime_hours']);
    }

    public function testWeeklyCapReclassifiesExcessRegularHoursAsOvertime(): void
    {
        // hours_per_week = 35 (not the usual 40) — proves the weekly cap is
        // fully config-driven. 5 days x (expected 8 / actual 9).
        $schedule = $this->schedule(['hours_per_week' => 35]);
        $daily = [];
        foreach (['2026-08-03', '2026-08-04', '2026-08-05', '2026-08-06', '2026-08-07'] as $date) {
            $daily[] = ['date' => $date, 'is_working_day' => true, 'expected_hours' => 8.0, 'actual_hours' => 9.0];
        }
        $result = PayrollCalculator::splitRegularOvertime($daily, $schedule);
        // Daily caps alone would give regular=40, overtime=5.
        // The weekly cap (35) reclassifies the extra 5 regular hours to overtime.
        $this->assertEquals(45.0, $result['actual_hours']);
        $this->assertEquals(35.0, $result['regular_hours']);
        $this->assertEquals(10.0, $result['overtime_hours']);
    }

    public function testHourlyPayCalculation(): void
    {
        $compensation = ['compensation_type' => 'HOURLY', 'hourly_rate' => 100, 'overtime_multiplier' => 1.5];
        $hours = ['expected_hours' => 40, 'actual_hours' => 45, 'regular_hours' => 40, 'overtime_hours' => 5];
        $pay = PayrollCalculator::calculatePay($hours, $this->schedule(), $compensation, 'WEEK');

        $this->assertEquals(4000.0, $pay['regular_pay']);   // 40 * 100
        $this->assertEquals(750.0, $pay['overtime_pay']);   // 5 * 100 * 1.5
        $this->assertEquals(4750.0, $pay['total_pay']);
    }

    public function testMonthlySalaryPaidInFullWhenFullyWorkedPlusOvertime(): void
    {
        // hours_per_week = 39 makes the implied monthly baseline a clean 169h
        // (39 * 52 / 12 = 169), and monthly_salary = 16900 makes the implied
        // hourly rate exactly 100 — chosen only so this test can assert exact
        // values instead of needing a float-delta comparison.
        $schedule = $this->schedule(['hours_per_week' => 39]);
        $compensation = ['compensation_type' => 'MONTHLY', 'monthly_salary' => 16900, 'overtime_multiplier' => 1.5];
        $hours = ['expected_hours' => 169, 'actual_hours' => 177, 'regular_hours' => 169, 'overtime_hours' => 8];

        $pay = PayrollCalculator::calculatePay($hours, $schedule, $compensation, 'MONTH');

        $this->assertEquals(16900.0, $pay['regular_pay']);
        $this->assertEquals(1200.0, $pay['overtime_pay']); // 8 * 100 * 1.5
        $this->assertEquals(18100.0, $pay['total_pay']);
    }

    public function testMonthlySalaryIsProratedWhenUnderworked(): void
    {
        $schedule = $this->schedule(['hours_per_week' => 39]);
        $compensation = ['compensation_type' => 'MONTHLY', 'monthly_salary' => 16900, 'overtime_multiplier' => 1.5];
        $hours = ['expected_hours' => 169, 'actual_hours' => 150, 'regular_hours' => 150, 'overtime_hours' => 0];

        $pay = PayrollCalculator::calculatePay($hours, $schedule, $compensation, 'MONTH');

        $this->assertEquals(15000.0, $pay['regular_pay']); // 16900 * (150/169)
        $this->assertEquals(0.0, $pay['overtime_pay']);
    }

    public function testMonthlySalaryProratesCorrectlyForAWeekPeriod(): void
    {
        $schedule = $this->schedule(['hours_per_week' => 39]);
        $compensation = ['compensation_type' => 'MONTHLY', 'monthly_salary' => 16900, 'overtime_multiplier' => 1.5];
        $hours = ['expected_hours' => 39, 'actual_hours' => 39, 'regular_hours' => 39, 'overtime_hours' => 0];

        $pay = PayrollCalculator::calculatePay($hours, $schedule, $compensation, 'WEEK');

        $this->assertEquals(3900.0, $pay['regular_pay']); // 16900 * 12 / 52
    }

    public function testWorkingDaysCsvParsing(): void
    {
        $this->assertEquals([1, 2, 3, 4, 5], PayrollCalculator::parseWorkingDays('1,2,3,4,5'));
        $this->assertEquals([6, 7], PayrollCalculator::parseWorkingDays('6,7'));
        $this->assertEquals([], PayrollCalculator::parseWorkingDays(''));
    }
}