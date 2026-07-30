<?php

namespace Innow\Controllers;

use Innow\Models\User;
use Innow\Utils\ResponseHelper;
use Innow\Utils\Validator;
use Innow\Middleware\AuthMiddleware;

class UserController {
    public function index(): void {
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

        ResponseHelper::success(['users' => User::all()]);
    }

    public function store(): void {
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

        $errors = Validator::validate($input, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'pin' => 'required|min:4',
            'department' => 'required',
        ]);

        if (!empty($errors)) {
            ResponseHelper::error('Validation failed', 422, $errors);
            return;
        }

        $existing = User::findByEmail($input['email']);
        if ($existing) {
            ResponseHelper::error('A staff member with this email already exists.', 400);
            return;
        }

        $newUser = User::create($input);
        ResponseHelper::success(['user' => $newUser], 'Staff member registered successfully.');
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
            ResponseHelper::error('Staff ID is required.', 400);
            return;
        }

        $target = User::find($id);
        if (!$target) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        if (($target['role'] ?? '') === 'System Administrator') {
            ResponseHelper::error('Cannot delete a System Administrator.', 403);
            return;
        }

        if (($target['id'] ?? '') === ($user['user_id'] ?? '')) {
            ResponseHelper::error('You cannot delete your own account.', 403);
            return;
        }

        $db = Database::getConnection();
        $db->prepare("DELETE FROM attendance_records WHERE user_id = :id")->execute(['id' => $id]);
        User::delete($id);
        ResponseHelper::success([], 'Staff member removed successfully.');
    }
}
