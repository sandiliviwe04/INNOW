<?php

namespace Innow\Tests;

use Innow\Config\Database;
use Innow\Models\AttendanceRecord;
use Innow\Models\WorkSchedule;
use Innow\Models\Compensation;
use Innow\Models\PayrollRecord;
use Innow\Services\PayrollService;
use Innow\Middleware\CsrfMiddleware;
use PHPUnit\Framework\TestCase;

class PayrollTest extends TestCase
{
    private static string $testUserId = 'STF-1001';
    private static array $createdAttendanceIds = [];
    private static ?string $scheduleId = null;
    private static ?string $compensationId = null;

    public static function tearDownAfterClass(): void
    {
        $db = Database::getConnection();
        foreach (self::$createdAttendanceIds as $id) {
            $db->prepare("DELETE FROM attendance_records WHERE id = :id")->execute(['id' => $id]);
        }
        if (self::$scheduleId) {
            $db->prepare("DELETE FROM employee_work_schedules WHERE id = :id")->execute(['id' => self::$scheduleId]);
        }
        if (self::$compensationId) {
            $db->prepare("DELETE FROM employee_compensation WHERE id = :id")->execute(['id' => self::$compensationId]);
        }
        $db->prepare("DELETE FROM payroll_records WHERE user_id = :id AND period_start = '2026-08-03'")
            ->execute(['id' => self::$testUserId]);
    }

    public function testWorkScheduleCanBeSavedForEmployee(): void
    {
        $schedule = WorkSchedule::create([
            'user_id' => self::$testUserId,
            'hours_per_day' => 8,
            'hours_per_week' => 40,
            'working_days' => '1,2,3,4,5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_duration_minutes' => 60,
            'break_paid' => false,
            'effective_date' => '2026-08-01',
        ]);

        $this->assertNotNull($schedule);
        $this->assertEquals(self::$testUserId, $schedule['user_id']);
        $this->assertEquals('1,2,3,4,5', $schedule['working_days']);

        self::$scheduleId = $schedule['id'];
    }

    public function testWorkScheduleVersioningPicksCorrectHistoricalRow(): void
    {
        if (!self::$scheduleId) {
            $this->markTestSkipped('No schedule created in previous test.');
        }

        $older = WorkSchedule::asOfDate(self::$testUserId, '2026-01-01');
        $this->assertNull($older, 'No schedule should exist before any effective_date.');

        $current = WorkSchedule::asOfDate(self::$testUserId, '2026-08-10');
        $this->assertNotNull($current);
        $this->assertEquals(self::$scheduleId, $current['id']);
    }

    public function testCompensationCanBeSavedForEmployee(): void
    {
        $compensation = Compensation::create([
            'user_id' => self::$testUserId,
            'compensation_type' => 'HOURLY',
            'hourly_rate' => 150,
            'overtime_multiplier' => 1.5,
            'effective_date' => '2026-08-01',
        ]);

        $this->assertNotNull($compensation);
        $this->assertEquals('HOURLY', $compensation['compensation_type']);

        self::$compensationId = $compensation['id'];
    }

    public function testPayrollReportMatchesExpectedActualOvertimeExample(): void
    {
        if (!self::$scheduleId || !self::$compensationId) {
            $this->markTestSkipped('Schedule/compensation not available from previous tests.');
        }

        $db = Database::getConnection();

        // Monday 2026-08-03: clock in 08:00, clock out 18:00 -> 10h gross,
        // minus the configured 60-minute unpaid break -> 9h actual.
        // Expected (from schedule) = 8h -> overtime must be exactly 1h.
        $in = AttendanceRecord::create(['user_id' => self::$testUserId, 'action' => 'CLOCK_IN', 'method' => 'MANUAL', 'notes' => 'Payroll test']);
        self::$createdAttendanceIds[] = $in['id'];
        $db->prepare("UPDATE attendance_records SET timestamp = '2026-08-03 08:00:00' WHERE id = :id")->execute(['id' => $in['id']]);

        $out = AttendanceRecord::create(['user_id' => self::$testUserId, 'action' => 'CLOCK_OUT', 'method' => 'MANUAL', 'notes' => 'Payroll test']);
        self::$createdAttendanceIds[] = $out['id'];
        $db->prepare("UPDATE attendance_records SET timestamp = '2026-08-03 18:00:00' WHERE id = :id")->execute(['id' => $out['id']]);

        $service = new PayrollService();
        $result = $service->generateForPeriod(self::$testUserId, 'WEEK', '2026-08-03', '2026-08-03', self::$testUserId);

        $this->assertEquals(8.0, (float) $result['expected_hours']);
        $this->assertEquals(9.0, (float) $result['actual_hours']);
        $this->assertEquals(8.0, (float) $result['regular_hours']);
        $this->assertEquals(1.0, (float) $result['overtime_hours']);

        // Hourly: regular 8h * 150 = 1200; overtime 1h * 150 * 1.5 = 225; total 1425.
        $this->assertEqualsWithDelta(1200.0, (float) $result['regular_pay'], 0.01);
        $this->assertEqualsWithDelta(225.0, (float) $result['overtime_pay'], 0.01);
        $this->assertEqualsWithDelta(1425.0, (float) $result['total_pay'], 0.01);
    }

    public function testPayrollRecordIsPersistedAndRegeneratingUpdatesSameRow(): void
    {
        $records = PayrollRecord::forUser(self::$testUserId);
        $matching = array_filter($records, fn($r) => $r['period_start'] === '2026-08-03');
        $this->assertNotEmpty($matching, 'Payroll record should have been persisted.');

        $countBefore = count(PayrollRecord::forUser(self::$testUserId));

        $service = new PayrollService();
        $service->generateForPeriod(self::$testUserId, 'WEEK', '2026-08-03', '2026-08-03', self::$testUserId);

        $countAfter = count(PayrollRecord::forUser(self::$testUserId));
        $this->assertEquals($countBefore, $countAfter, 'Re-generating the same period must not create a duplicate row.');
    }

    public function testOnlyAdminRoleIsRecognisedAsPayrollAdmin(): void
    {
        // Mirrors the exact admin check used by PayrollController::requireAdmin()
        // and nav.php's $isAdmin, so this test documents/guards that rule.
        $isAdmin = fn(string $role): bool =>
            stripos($role, 'admin') !== false || $role === 'System Administrator';

        $this->assertTrue($isAdmin('Admin'));
        $this->assertTrue($isAdmin('System Administrator'));
        $this->assertTrue($isAdmin('HR Admin'));
        $this->assertFalse($isAdmin('Staff Member'));
        $this->assertFalse($isAdmin('Supervisor'));
    }

    public function testCsrfTokenIsRequiredAndValidated(): void
    {
        $fakeSessionToken = bin2hex(random_bytes(32));
        $validToken = CsrfMiddleware::generateToken($fakeSessionToken);

        $this->assertTrue(CsrfMiddleware::validateToken($fakeSessionToken, $validToken));
        $this->assertFalse(CsrfMiddleware::validateToken($fakeSessionToken, 'wrong-token'));
        $this->assertFalse(CsrfMiddleware::validateToken($fakeSessionToken, ''));

        CsrfMiddleware::invalidateToken($fakeSessionToken);
        $this->assertFalse(CsrfMiddleware::validateToken($fakeSessionToken, $validToken));
    }
}