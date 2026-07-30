<?php

namespace Innow\Services;

use Innow\Models\User;
use Innow\Models\AttendanceRecord;

class AttendanceService {

    public function __construct() {
    }

    /**
     * Toggles attendance status for a staff member (Clock-In vs Clock-Out / Break)
     */
    public function toggle(string $userId, string $method = 'BUTTON', ?string $customAction = null, string $notes = ''): array {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }

        $currentStatus = $user['status'];
        $nextStatus = 'ONSITE';
        $action = 'CLOCK_IN';

        if ($customAction) {
            $action = strtoupper($customAction);
            switch ($action) {
                case 'CLOCK_IN':
                    $nextStatus = 'ONSITE';
                    break;
                case 'CLOCK_OUT':
                    $nextStatus = 'OFFSITE';
                    break;
                case 'BREAK_START':
                    $nextStatus = 'BREAK';
                    break;
                case 'BREAK_END':
                    $nextStatus = 'ONSITE';
                    break;
            }
        } else {
            // Automatic toggle based on state
            if ($currentStatus === 'OFFSITE') {
                $nextStatus = 'ONSITE';
                $action = 'CLOCK_IN';
            } else if ($currentStatus === 'ONSITE') {
                $nextStatus = 'OFFSITE';
                $action = 'CLOCK_OUT';
            } else if ($currentStatus === 'BREAK') {
                $nextStatus = 'ONSITE';
                $action = 'BREAK_END';
            }
        }

        // Prevent duplicate consecutive same actions within 5 seconds
        $latestLog = AttendanceRecord::getLatestForUser($userId);
        if ($latestLog && $latestLog['action'] === $action && (time() - strtotime($latestLog['timestamp'])) < 5) {
            return [
                'success' => true,
                'already_recorded' => true,
                'message' => "Action '{$action}' already recorded recently.",
                'user' => User::find($userId),
                'record' => $latestLog,
            ];
        }

        // Update User Status
        User::updateStatus($userId, $nextStatus);

        // Create Attendance Log
        $record = AttendanceRecord::create([
            'user_id' => $userId,
            'action' => $action,
            'method' => $method,
            'notes' => $notes ?: "Terminal {$method} Check-in/out",
        ]);

        $record['staff_name'] = $user['name'];
        $record['department'] = $user['department'];
        $record['role'] = $user['role'];

        $updatedUser = User::find($userId);

        return [
            'success' => true,
            'action' => $action,
            'prev_status' => $currentStatus,
            'new_status' => $nextStatus,
            'method' => $method,
            'user' => $updatedUser,
            'record' => $record,
        ];
    }
}
