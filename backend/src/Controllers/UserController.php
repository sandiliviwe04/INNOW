<?php

namespace Innow\Controllers;

use Innow\Models\User;
use Innow\Utils\ResponseHelper;
use Innow\Utils\Validator;
use Innow\Middleware\AuthMiddleware;
use Innow\Config\Database;

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

    public function update(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $this->validateCsrf();

        // Multipart requests (used when a photo is attached) populate $_POST
        // directly; plain JSON requests don't, so fall back to reading the
        // raw body in that case.
        $input = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);
        $id = trim($input['id'] ?? '');

        if (!$id) {
            ResponseHelper::error('Staff ID is required.', 400);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';

        // Staff members may only edit their own profile. Admins may edit anyone's.
        if (!$isAdmin && $id !== ($user['user_id'] ?? '')) {
            ResponseHelper::error('You can only edit your own profile.', 403);
            return;
        }

        $target = User::find($id);
        if (!$target) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        $errors = Validator::validate($input, [
            'name' => 'required|min:2',
            'email' => 'required|email',
        ]);

        if (!empty($errors)) {
            ResponseHelper::error('Validation failed', 422, $errors);
            return;
        }

        $existing = User::findByEmail($input['email']);
        if ($existing && $existing['id'] !== $id) {
            ResponseHelper::error('Another staff member is already using this email.', 400);
            return;
        }

        $updateData = [
            'name' => trim($input['name']),
            'email' => trim($input['email']),
            'phone' => trim($input['phone'] ?? ''),
            'emergency_contact' => trim($input['emergency_contact'] ?? ''),
            'address' => trim($input['address'] ?? ''),
        ];

        // Handle an attached profile photo, if any.
        if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['avatar']['tmp_name']);

            if (!isset($allowedTypes[$mime])) {
                ResponseHelper::error('Invalid image type. Please upload a JPG, PNG, or WEBP image.', 400);
                return;
            }

            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                ResponseHelper::error('Image is too large. Maximum size is 2MB.', 400);
                return;
            }

            $ext = $allowedTypes[$mime];
            $filename = $id . '_' . time() . '.' . $ext;
            $destDir = __DIR__ . '/../../../assets/avatars/';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $destPath = $destDir . $filename;

            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                ResponseHelper::error('Failed to save the uploaded photo. Please try again.', 500);
                return;
            }

            // Clean up the old photo file, if one existed, to avoid orphaned files piling up.
            if (!empty($target['avatar_url'])) {
                $oldPath = __DIR__ . '/../../../' . ltrim($target['avatar_url'], '/');
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $updateData['avatar_url'] = '/assets/avatars/' . $filename;
        }

        $updated = User::update($id, $updateData);

        ResponseHelper::success(['user' => $updated], 'Staff details updated successfully.');
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

    public function resetPin(): void
    {
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

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = trim($input['id'] ?? '');
        $newPin = trim($input['pin'] ?? '');

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
            ResponseHelper::error('Cannot reset the PIN of a System Administrator.', 403);
            return;
        }

        if ($newPin === '') {
            $newPin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        }

        if (!preg_match('/^\d{4}$/', $newPin)) {
            ResponseHelper::error('PIN must be exactly 4 digits.', 400);
            return;
        }

        $hashedPin = password_hash($newPin, PASSWORD_BCRYPT);

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET pin = :pin WHERE id = :id");
        $stmt->execute([
            'pin' => $hashedPin,
            'id' => $id,
        ]);

        ResponseHelper::success([
            'pin' => $newPin,
        ], 'PIN reset successfully.');
    }

    private function validateCsrf(): void
    {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }
        $sessionToken = $user['token'] ?? '';
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!$sessionToken || !$clientToken || !\Innow\Middleware\CsrfMiddleware::validateToken($sessionToken, $clientToken)) {
            ResponseHelper::error('Invalid or missing CSRF token. Please refresh the page and try again.', 403);
            return;
        }
    }
}