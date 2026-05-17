<?php
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthMiddleware {
    public static function validate(): array {
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!$auth || !str_starts_with($auth, 'Bearer '))
            Response::error('Token tidak ditemukan. Silakan login terlebih dahulu.', 401);

        $payload = JWT::decode(substr($auth, 7));
        if (!$payload)
            Response::error('Token tidak valid atau sudah kedaluwarsa. Silakan login kembali.', 401);

        return $payload;
    }

    public static function requireRole(array $payload, array|string $roles): void {
        if (!in_array($payload['role'], (array) $roles, true))
            Response::error('Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.', 403);
    }
}
