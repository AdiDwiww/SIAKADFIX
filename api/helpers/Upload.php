<?php
class Upload {
    private static array  $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    private static int    $maxSize = 5_242_880;            // 5 MB
    private static string $base    = '';

    private static function getBase(): string {
        if (!self::$base) {
            // __DIR__ = api/helpers  →  go up 2 levels to project root
            self::$base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        }
        return self::$base;
    }

    /**
     * @param  array  $file       $_FILES['foto']
     * @param  string $subfolder  e.g. 'mahasiswa' or 'dosen'
     * @return string|null        relative path stored in DB, e.g. 'mahasiswa/abc123.jpg'
     */
    public static function photo(array $file, string $subfolder = 'foto'): ?string {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;

        if (!in_array($file['type'], self::$allowed, true))
            throw new RuntimeException('Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');

        if ($file['size'] > self::$maxSize)
            throw new RuntimeException('Ukuran file melebihi batas maksimal 5 MB.');

        $dir = self::getBase() . $subfolder . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('', true) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename))
            throw new RuntimeException('Gagal menyimpan file. Periksa izin folder uploads/.');

        return $subfolder . '/' . $filename;
    }

    public static function delete(?string $relativePath): void {
        if (!$relativePath) return;
        $full = self::getBase() . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (file_exists($full)) unlink($full);
    }
}
