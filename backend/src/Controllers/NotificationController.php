<?php

namespace Innow\Controllers;

use Innow\Config\Database;
use Innow\Middleware\AuthMiddleware;
use Innow\Utils\ResponseHelper;

class NotificationController {

    // Returns activity (announcements + attendance actions) that happened after
    // the given cursor timestamp, excluding the current user's own actions.
    // Polled by the frontend every few seconds while a user is logged in.
    public function poll(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $myId = $user['user_id'] ?? '';
        $since = $_GET['since'] ?? null;
        $db = Database::getConnection();

        // Always compute "now" from the DB server itself, not PHP/client time,
        // so the next cursor the client sends back is consistent with the
        // timestamps stored by MySQL (avoids clock-drift gaps or duplicates).
        $serverTimeRow = $db->query("SELECT NOW(6) as now_ts")->fetch();
        $serverTime = $serverTimeRow['now_ts'];

        // First-ever poll for this page load: just hand back the current
        // server time as the starting cursor, without dumping historical
        // events as a flood of toasts the moment someone logs in.
        if (!$since) {
            ResponseHelper::success([
                'server_time' => $serverTime,
                'events' => [],
            ]);
            return;
        }

        $events = [];

        $stmt = $db->prepare("
            SELECT a.id, a.title, a.message, a.created_at, u.name AS author_name
            FROM announcements a
            JOIN users u ON u.id = a.user_id
            WHERE a.created_at > :since AND a.user_id != :myId
            ORDER BY a.created_at ASC
            LIMIT 20
        ");
        $stmt->execute(['since' => $since, 'myId' => $myId]);
        foreach ($stmt->fetchAll() as $row) {
            $events[] = [
                'type' => 'announcement',
                'time' => $row['created_at'],
                'title' => $row['title'],
                'message' => $row['message'],
                'author' => $row['author_name'],
            ];
        }

        $stmt = $db->prepare("
            SELECT ar.id, ar.action, ar.timestamp, u.name AS staff_name
            FROM attendance_records ar
            JOIN users u ON u.id = ar.user_id
            WHERE ar.timestamp > :since AND ar.user_id != :myId
            ORDER BY ar.timestamp ASC
            LIMIT 20
        ");
        $stmt->execute(['since' => $since, 'myId' => $myId]);
        foreach ($stmt->fetchAll() as $row) {
            $events[] = [
                'type' => 'attendance',
                'time' => $row['timestamp'],
                'staff_name' => $row['staff_name'],
                'action' => $row['action'],
            ];
        }

        usort($events, fn($a, $b) => strcmp($a['time'], $b['time']));

        ResponseHelper::success([
            'server_time' => $serverTime,
            'events' => $events,
        ]);
    }
}