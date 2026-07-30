<?php

namespace Innow\Controllers;

use Innow\Models\LeaveRequest;
use Innow\Utils\ResponseHelper;
use Innow\Middleware\AuthMiddleware;

class LeaveController {
    public function index(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        $userId = $user['user_id'] ?? ($user['id'] ?? '');
        $leaves = LeaveRequest::forUser($userId, $isAdmin);
        ResponseHelper::success(['leaves' => $leaves]);
    }

    public function store(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $leaveType = trim($input['leave_type'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $daysRequested = (int)($input['days_requested'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$leaveType || !$startDate || !$endDate || $daysRequested <= 0) {
            ResponseHelper::error('Please fill all required fields correctly.', 400);
            return;
        }

        if ($leaveType === 'Other' && trim($reason) === '') {
            ResponseHelper::error('Please provide a reason for your leave request.', 400);
            return;
        }

        $leave = LeaveRequest::create([
            'user_id' => $user['user_id'],
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_requested' => $daysRequested,
            'reason' => $reason,
        ]);

        ResponseHelper::success(['leave' => $leave], 'Leave request submitted successfully.');
    }

    public function update(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        if (!$isAdmin) {
            ResponseHelper::error('Forbidden', 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = trim($input['id'] ?? '');
        $status = trim($input['status'] ?? '');

        if (!$id || !$status) {
            ResponseHelper::error('Invalid request.', 400);
            return;
        }

        $leave = LeaveRequest::find($id);
        if (!$leave) {
            ResponseHelper::error('Leave request not found.', 404);
            return;
        }

        LeaveRequest::updateStatus($id, $status, $user['user_id']);
        ResponseHelper::success([], "Leave request {$status}.");
    }

    public function destroy(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        if (!$isAdmin) {
            ResponseHelper::error('Forbidden', 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = trim($input['id'] ?? '');

        if (!$id) {
            ResponseHelper::error('Leave ID is required.', 400);
            return;
        }

        LeaveRequest::delete($id);
        ResponseHelper::success([], 'Leave request deleted.');
    }
}
