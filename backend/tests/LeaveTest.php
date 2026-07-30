<?php

namespace Innow\Tests;

use Innow\Models\User;
use Innow\Models\LeaveRequest;
use PHPUnit\Framework\TestCase;

class LeaveTest extends TestCase
{
    private static string $testUserId = 'STF-1001';
    private static ?string $createdLeaveId = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$createdLeaveId) {
            LeaveRequest::delete(self::$createdLeaveId);
        }
    }

    public function testLeaveCanBeCreated(): void
    {
        $leave = LeaveRequest::create([
            'user_id' => self::$testUserId,
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'days_requested' => 5,
            'reason' => 'Unit test leave'
        ]);

        $this->assertNotNull($leave);
        $this->assertEquals('PENDING', $leave['status']);
        $this->assertEquals(self::$testUserId, $leave['user_id']);

        self::$createdLeaveId = $leave['id'];
    }

    public function testLeaveStatusCanBeUpdated(): void
    {
        if (!self::$createdLeaveId) {
            $this->markTestSkipped('No leave ID available from previous test.');
        }

        $updated = LeaveRequest::updateStatus(self::$createdLeaveId, 'APPROVED', self::$testUserId);
        $this->assertTrue($updated);

        $leave = LeaveRequest::find(self::$createdLeaveId);
        $this->assertEquals('APPROVED', $leave['status']);
        $this->assertEquals(self::$testUserId, $leave['reviewed_by']);
    }

    public function testLeaveIsVisibleToOwner(): void
    {
        if (!self::$createdLeaveId) {
            $this->markTestSkipped('No leave ID available from previous test.');
        }

        $leaves = LeaveRequest::forUser(self::$testUserId, false);
        $ids = array_column($leaves, 'id');
        $this->assertContains(self::$createdLeaveId, $ids);
    }

    public function testLeaveIsVisibleToAdmin(): void
    {
        if (!self::$createdLeaveId) {
            $this->markTestSkipped('No leave ID available from previous test.');
        }

        $leaves = LeaveRequest::forUser(self::$testUserId, true);
        $ids = array_column($leaves, 'id');
        $this->assertContains(self::$createdLeaveId, $ids);
    }

    public function testLeaveCanBeDeleted(): void
    {
        if (!self::$createdLeaveId) {
            $this->markTestSkipped('No leave ID available from previous test.');
        }

        $deleted = LeaveRequest::delete(self::$createdLeaveId);
        $this->assertTrue($deleted);

        $leave = LeaveRequest::find(self::$createdLeaveId);
        $this->assertNull($leave);

        self::$createdLeaveId = null;
    }
}
