<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Response.php';

class ProfileHelper {
    public static function getMahasiswaId(PDO $db, array $payload): int {
        $st = $db->prepare("SELECT id FROM mahasiswa WHERE user_id = ?");
        $st->execute([$payload['id']]);
        $row = $st->fetch();
        if (!$row) Response::error('Profil mahasiswa tidak ditemukan. Hubungi admin.', 404);
        return (int) $row['id'];
    }

    public static function getDosenId(PDO $db, array $payload): int {
        $st = $db->prepare("SELECT id FROM dosen WHERE user_id = ?");
        $st->execute([$payload['id']]);
        $row = $st->fetch();
        if (!$row) Response::error('Profil dosen tidak ditemukan. Hubungi admin.', 404);
        return (int) $row['id'];
    }

    public static function getMahasiswaProfile(PDO $db, array $payload): ?array {
        $st = $db->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
        $st->execute([$payload['id']]);
        return $st->fetch() ?: null;
    }

    public static function getDosenProfile(PDO $db, array $payload): ?array {
        $st = $db->prepare("SELECT * FROM dosen WHERE user_id = ?");
        $st->execute([$payload['id']]);
        return $st->fetch() ?: null;
    }
}
