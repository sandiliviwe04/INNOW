<?php

namespace Innow\Tests;

use Innow\Models\Announcement;
use Innow\Models\User;
use PHPUnit\Framework\TestCase;

class AnnouncementTest extends TestCase
{
    private static string $adminUserId = 'STF-1001';
    private static ?string $createdAnnouncementId = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$createdAnnouncementId) {
            Announcement::delete(self::$createdAnnouncementId);
        }
    }

    public function testAnnouncementCanBeCreated(): void
    {
        $ann = Announcement::create([
            'user_id' => self::$adminUserId,
            'title' => 'PHPUnit Test Announcement',
            'message' => 'This is a test announcement from the test suite.'
        ]);

        $this->assertNotNull($ann);
        $this->assertEquals('PHPUnit Test Announcement', $ann['title']);
        $this->assertEquals(self::$adminUserId, $ann['user_id']);

        self::$createdAnnouncementId = $ann['id'];
    }

    public function testAnnouncementIsVisibleToAll(): void
    {
        if (!self::$createdAnnouncementId) {
            $this->markTestSkipped('No announcement ID available from previous test.');
        }

        $announcements = Announcement::all();
        $ids = array_column($announcements, 'id');
        $this->assertContains(self::$createdAnnouncementId, $ids);
    }

    public function testAnnouncementCanBeDeleted(): void
    {
        if (!self::$createdAnnouncementId) {
            $this->markTestSkipped('No announcement ID available from previous test.');
        }

        $deleted = Announcement::delete(self::$createdAnnouncementId);
        $this->assertTrue($deleted);

        $ann = Announcement::find(self::$createdAnnouncementId);
        $this->assertNull($ann);

        self::$createdAnnouncementId = null;
    }
}
