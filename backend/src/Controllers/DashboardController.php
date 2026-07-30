<?php

namespace Innow\Controllers;

use Innow\Models\User;
use Innow\Models\AttendanceRecord;
use Innow\Utils\ResponseHelper;
use Innow\Middleware\AuthMiddleware;

class DashboardController {
    public function getSummary(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $users = User::all();
        $logs = AttendanceRecord::all();

        $onsite = array_filter($users, fn($u) => $u['status'] === 'ONSITE');
        $onBreak = array_filter($users, fn($u) => $u['status'] === 'BREAK');
        $offsite = array_filter($users, fn($u) => $u['status'] === 'OFFSITE');

        ResponseHelper::success([
            'metrics' => [
                'total_staff' => count($users),
                'onsite_count' => count($onsite),
                'break_count' => count($onBreak),
                'offsite_count' => count($offsite),
                'total_today_logs' => count($logs),
            ],
            'onsite_staff' => array_values($onsite),
            'all_staff' => $users,
            'recent_logs' => array_slice($logs, 0, 15),
        ]);
    }
}
