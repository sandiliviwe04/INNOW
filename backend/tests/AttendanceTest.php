<?php

namespace Innow\Tests;

use Innow\Models\User;
use Innow\Models\AttendanceRecord;
use PHPUnit\Framework\TestCase;

class AttendanceTest extends TestCase
{
    private static string $testUserId = 'STF-1001';

    public function testClockInCreatesRecord(): void
    {
        // Beginning-of-day guard: if already clocked in, clock out first
        $existing = AttendanceRecord::all();
        foreach ($existing as $record) {
            if ($record['user_id'] === self::$testUserId && $record['action'] === 'CLOCK_IN') {
                AttendanceRecord::create([
                    'user_id' => self::$testUserId,
                    'action' => 'CLOCK_OUT',
                    'method' => 'BUTTON',
                    'notes' => 'Test cleanup clock-out'
                ]);
                break;
            }
        }

        $record = AttendanceRecord::create([
            'user_id' => self::$testUserId,
            'action' => 'CLOCK_IN',
            'method' => 'BUTTON',
            'notes' => 'Unit test clock-in'
        ]);

        $this->assertNotNull($record);
        $this->assertEquals('CLOCK_IN', $record['action']);
        $this->assertEquals('BUTTON', $record['method']);
    }

    public function testClockOutCreatesRecord(): void
    {
        $record = AttendanceRecord::create([
            'user_id' => self::$testUserId,
            'action' => 'CLOCK_OUT',
            'method' => 'BUTTON',
            'notes' => 'Unit test clock-out'
        ]);

        $this->assertNotNull($record);
        $this->assertEquals('CLOCK_OUT', $record['action']);
    }

    public function testBreaksAreTracked(): void
    {
        $record = AttendanceRecord::create([
            'user_id' => self::$testUserId,
            'action' => 'BREAK_START',
            'method' => 'BUTTON',
            'notes' => 'Unit test break'
        ]);

        $this->assertNotNull($record);
        $this->assertEquals('BREAK_START', $record['action']);
    }

    public function testAttendanceRecordHasTimestamp(): void
    {
        $record = AttendanceRecord::create([
            'user_id' => self::$testUserId,
            'action' => 'CLOCK_IN',
            'method' => 'QR',
            'notes' => 'QR test'
        ]);

        $this->assertNotNull($record);
        $this->assertNotEmpty($record['timestamp']);
    }

    public function testAllRecordsCanBeFetched(): void
    {
        $records = AttendanceRecord::all();
        $this->assertIsArray($records);
        $this->assertNotEmpty($records);
    }
}
