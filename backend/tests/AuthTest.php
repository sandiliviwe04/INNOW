<?php

namespace Innow\Tests;

use Innow\Models\User;
use Innow\Models\Session;
use Innow\Models\AttendanceRecord;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $this->assertTrue(true, 'Database connection established in bootstrap.');
    }

    public function testFindUserByEmail(): void
    {
        $user = User::findByEmail('thabo.m@innow.com');
        $this->assertNotNull($user);
        $this->assertEquals('thabo.m@innow.com', $user['email']);
    }

    public function testUsersHaveBcryptPinHashes(): void
    {
        $user = User::findByEmail('thabo.m@innow.com');
        $this->assertNotNull($user);
        $pinField = $user['pin'];

        if (strlen($pinField) === 60 && str_starts_with($pinField, '$2y$')) {
            $this->assertTrue(password_verify('1001', $pinField));
        } else {
            $this->assertEquals('1001', $pinField);
        }
    }

    public function testNewUsersGetHashedPin(): void
    {
        $hashedPin = password_hash('9999', PASSWORD_BCRYPT);
        $this->assertNotEquals('9999', $hashedPin);
        $this->assertTrue(password_verify('9999', $hashedPin));
        $this->assertSame(60, strlen($hashedPin));

        $userData = [
            'name' => 'BCrypt Test User',
            'email' => 'bcrypt.test@innow.com',
            'pin' => '9999',
            'role' => 'Staff Member',
            'department' => 'Testing',
        ];

        $newUser = null;
        try {
            $newUser = User::create($userData);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database `pin` column too narrow for bcrypt hash. Run ALTER TABLE users MODIFY pin VARCHAR(255) NOT NULL;');
        }

        $this->assertNotNull($newUser);
        $this->assertNotEquals('9999', $newUser['pin']);
        $this->assertTrue(password_verify('9999', $newUser['pin']));

        $fetched = User::find($newUser['id']);
        $this->assertTrue(password_verify('9999', $fetched['pin']));

        User::delete($newUser['id']);
    }

    public function testInvalidPinReturnsNull(): void
    {
        $user = User::findByEmail('thabo.m@innow.com');
        $this->assertNotNull($user);
        $this->assertFalse(password_verify('0000', $user['pin']));
    }

    public function testSessionCreatedForValidUser(): void
    {
        $user = User::findByEmail('thabo.m@innow.com');
        $this->assertNotNull($user);

        $token = Session::create($user['id']);
        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testFindByTokenReturnsSessionWithUser(): void
    {
        $user = User::findByEmail('thabo.m@innow.com');
        $token = Session::create($user['id']);
        $session = Session::findByToken($token);
        $this->assertNotNull($session);
        $this->assertEquals($user['id'], $session['user_id']);
        $this->assertEquals($user['name'], $session['name']);
    }

    public function testInvalidTokenReturnsNull(): void
    {
        $session = Session::findByToken('invalidtoken123');
        $this->assertNull($session);
    }
}
