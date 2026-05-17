<?php
/**
 * Setup Script – buat/reset akun admin
 * Akses: http://localhost/uas/setup.php
 * HAPUS file ini setelah selesai setup!
 */
require_once __DIR__ . '/api/config/Database.php';

$db = (new Database())->getConnection();

$username = 'admin';
$password = 'admin123';
$email    = 'admin@sia.ac.id';
$role     = 'ADMIN';
$hash     = password_hash($password, PASSWORD_BCRYPT);

// Hapus admin lama jika ada, lalu insert ulang
$db->prepare("DELETE FROM users WHERE username = ?")->execute([$username]);
$db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)")
   ->execute([$username, $hash, $email, $role]);

echo "<h2 style='font-family:sans-serif;color:green'>✅ Akun admin berhasil dibuat!</h2>";
echo "<p style='font-family:sans-serif'>Username: <b>$username</b> | Password: <b>$password</b></p>";
echo "<p style='font-family:sans-serif'><a href='pages/login.html'>→ Klik di sini untuk Login</a></p>";
echo "<p style='font-family:sans-serif;color:red'><b>⚠️ Hapus file setup.php setelah login!</b></p>";
