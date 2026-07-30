<?php

namespace Innow\Utils;

class ResponseHelper {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public static function error(string $message, int $statusCode = 400, array $errors = []): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function success(array $data = [], string $message = 'Success'): void {
        self::json(array_merge([
            'success' => true,
            'message' => $message,
        ], is_array($data) ? $data : []), 200);
    }
}
