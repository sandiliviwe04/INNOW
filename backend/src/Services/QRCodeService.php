<?php

namespace Innow\Services;

class QRCodeService {
    private string $secret;
    private int $validitySeconds;

    public function __construct() {
        $config = require __DIR__ . '/../../config/app.php';
        $this->secret = $config['secret'];
        $this->validitySeconds = $config['qr_validity_seconds'] ?? 30;
    }

    /**
     * Generates a signed, short-lived payload string for the scanner display
     */
    public function generatePayload(string $terminalId = 'TRM-GATE-1'): array {
        $timestamp = time();
        $expiresAt = $timestamp + $this->validitySeconds;
        $raw = "{$terminalId}|{$timestamp}|{$expiresAt}";
        $signature = hash_hmac('sha256', $raw, $this->secret);
        $token = base64_encode("{$raw}|{$signature}");

        return [
            'token' => $token,
            'terminal_id' => $terminalId,
            'timestamp' => $timestamp,
            'expires_at' => $expiresAt,
            'ttl_seconds' => $this->validitySeconds,
            'scan_url' => '/checkin/qr?token=' . urlencode($token),
        ];
    }

    /**
     * Verifies the HMAC payload and checks expiry
     */
    public function verifyPayload(string $token): ?array {
        $decoded = base64_decode($token);
        if (!$decoded) return null;

        $parts = explode('|', $decoded);
        if (count($parts) !== 4) return null;

        list($terminalId, $timestamp, $expiresAt, $providedSignature) = $parts;

        $raw = "{$terminalId}|{$timestamp}|{$expiresAt}";
        $expectedSignature = hash_hmac('sha256', $raw, $this->secret);

        if (!hash_equals($expectedSignature, $providedSignature)) {
            return null; // Tampered payload
        }

        if (time() > (int)$expiresAt) {
            return ['error' => 'EXPIRED', 'message' => 'QR Code expired. Please scan the current terminal display code.'];
        }

        return [
            'valid' => true,
            'terminal_id' => $terminalId,
            'timestamp' => (int)$timestamp,
            'expires_at' => (int)$expiresAt,
        ];
    }
}
