<?php

namespace Innow\Controllers;

use Innow\Services\QRCodeService;
use Innow\Services\AttendanceService;
use Innow\Models\User;
use Innow\Models\AttendanceRecord;
use Innow\Utils\ResponseHelper;
use Innow\Middleware\AuthMiddleware;
use Innow\Middleware\CsrfMiddleware;
use Innow\Middleware\RateLimiter;

class AttendanceController {
    private QRCodeService $qrService;
    private AttendanceService $attendanceService;

    public function __construct() {
        $this->qrService = new QRCodeService();
        $this->attendanceService = new AttendanceService();
    }

    public function getQRPayload(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::check('qr:' . $clientIp)) {
            ResponseHelper::error('Rate limit exceeded. Please wait a moment.', 429);
            return;
        }
        $terminalId = $_GET['terminal_id'] ?? $_GET['kiosk_id'] ?? 'TRM-MAIN-GATE';
        $payload = $this->qrService->generatePayload($terminalId);
        ResponseHelper::success(['payload' => $payload], 'Active QR payload generated.');
    }

    public function checkinQR(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        $this->validateCsrf();
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::check('checkin:' . $clientIp)) {
            ResponseHelper::error('Rate limit exceeded. Please wait a moment.', 429);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $token = trim($input['qr_token'] ?? $input['payload_token'] ?? '');
        $requestedUserId = trim($input['user_id'] ?? '');

        if (!$token && !$requestedUserId) {
            ResponseHelper::error('Missing QR payload token or User ID.', 400);
            return;
        }

        if ($token) {
            $verification = $this->qrService->verifyPayload($token);
            if (!$verification) {
                ResponseHelper::error('Invalid or tampered QR Code payload.', 400);
                return;
            }
            if (isset($verification['error'])) {
                ResponseHelper::error($verification['message'], 400);
                return;
            }
        }

        if (!$isAdmin) {
            $userId = $user['user_id'];
        } else {
            $userId = $requestedUserId;
            if (!$userId && isset($input['qr_data'])) {
                $foundUser = User::find($input['qr_data']);
                if ($foundUser) {
                    $userId = $foundUser['id'];
                }
            }
        }

        if (!$userId) {
            $userId = 'STF-1002';
        }

        $notes = 'Scanned via Front Gate Camera';
        if ($token && isset($verification['terminal_id'])) {
            $notes = 'QR Scan at ' . $verification['terminal_id'];
        }

        $result = $this->attendanceService->toggle($userId, 'QR', $input['action'] ?? null, $notes);
        ResponseHelper::json($result, $result['success'] ? 200 : 400);
    }

    public function checkinButton(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        $this->validateCsrf();
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::check('checkin:' . $clientIp)) {
            ResponseHelper::error('Rate limit exceeded. Please wait a moment.', 429);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $requestedUserId = trim($input['user_id'] ?? '');

        if (!$isAdmin) {
            $userId = $user['user_id'];
        } else {
            $userId = $requestedUserId;
            if (!$userId) {
                $userId = $input['staff_id'] ?? 'STF-1001';
            }
        }

        $action = $input['action'] ?? null;
        $notes = trim($input['notes'] ?? 'One-click Check-in Button');

        if (!empty($input['qr_token'])) {
            $verification = $this->qrService->verifyPayload($input['qr_token']);
            if (!$verification || isset($verification['error'])) {
                ResponseHelper::error('Invalid or expired QR code.', 400);
                return;
            }
            $notes = 'QR Scan at ' . ($verification['terminal_id'] ?? 'terminal');
        }

        $result = $this->attendanceService->toggle($userId, 'BUTTON', $action, $notes);
        ResponseHelper::json($result, $result['success'] ? 200 : 400);
    }

    public function manualEntry(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }
        $this->validateCsrf();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $userId = trim($input['user_id'] ?? '');
        $action = trim($input['action'] ?? 'CLOCK_IN');
        $notes = trim($input['notes'] ?? 'Manual Admin Entry');

        if (!$userId) {
            ResponseHelper::error('Please select a staff member.', 400);
            return;
        }

        $result = $this->attendanceService->toggle($userId, 'MANUAL', $action, $notes);
        ResponseHelper::json($result, $result['success'] ? 200 : 400);
    }

    private function validateCsrf(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }
        $sessionToken = $user['token'] ?? '';
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!$sessionToken || !$clientToken || !CsrfMiddleware::validateToken($sessionToken, $clientToken)) {
            ResponseHelper::error('Invalid or missing CSRF token. Please refresh the page and try again.', 403);
            return;
        }
    }
}
