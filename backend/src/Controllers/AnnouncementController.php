<?php

namespace Innow\Controllers;

use Innow\Models\Announcement;
use Innow\Utils\ResponseHelper;
use Innow\Middleware\AuthMiddleware;

class AnnouncementController {
    public function index(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $announcements = Announcement::all();
        ResponseHelper::success(['announcements' => $announcements]);
    }

    public function store(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $title = trim($input['title'] ?? '');
        $message = trim($input['message'] ?? '');

        if (!$title || !$message) {
            ResponseHelper::error('Title and message are required.', 400);
            return;
        }

        $announcement = Announcement::create([
            'user_id' => $user['user_id'],
            'title' => $title,
            'message' => $message,
        ]);

        ResponseHelper::success(['announcement' => $announcement], 'Announcement posted successfully.');
    }

    public function destroy(): void {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = trim($input['id'] ?? '');

        if (!$id) {
            ResponseHelper::error('Announcement ID is required.', 400);
            return;
        }

        $announcement = Announcement::find($id);
        if (!$announcement) {
            ResponseHelper::error('Announcement not found.', 404);
            return;
        }

        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        $isOwner = $announcement['user_id'] === $user['user_id'];

        if (!$isAdmin && !$isOwner) {
            ResponseHelper::error('Forbidden', 403);
            return;
        }

        Announcement::delete($id);
        ResponseHelper::success([], 'Announcement deleted.');
    }
}
