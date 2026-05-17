<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* POST /api/auth/login */
    public function login(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($d['username'] ?? '');
        $password = $d['password'] ?? '';

        if (!$username || !$password)
            Response::error('Username dan password wajib diisi.', 422);

        $st = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $st->execute([$username]);
        $user = $st->fetch();

        if (!$user || !password_verify($password, $user['password']))
            Response::error('Username atau password salah.', 401);

        $token = JWT::encode([
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);

        // fetch linked profile
        $profile = null;
        if ($user['role'] === 'DOSEN') {
            $ps = $this->db->prepare("SELECT * FROM dosen WHERE user_id = ?");
            $ps->execute([$user['id']]);
            $profile = $ps->fetch() ?: null;
        } elseif ($user['role'] === 'MAHASISWA') {
            $ps = $this->db->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
            $ps->execute([$user['id']]);
            $profile = $ps->fetch() ?: null;
        }

        Response::success([
            'token'   => $token,
            'user'    => ['id' => $user['id'], 'username' => $user['username'],
                          'email' => $user['email'], 'role' => $user['role']],
            'profile' => $profile,
        ], 'Login berhasil!');
    }

    /* POST /api/auth/register */
    public function register(): void {
        $d        = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($d['username'] ?? '');
        $password = $d['password'] ?? '';
        $email    = trim($d['email']    ?? '');
        $role     = $d['role'] ?? 'MAHASISWA';

        if (!$username || !$password || !$email)
            Response::error('Username, password, dan email wajib diisi.', 422);

        if (strlen($password) < 6)
            Response::error('Password minimal 6 karakter.', 422);

        if (!in_array($role, ['ADMIN','DOSEN','MAHASISWA'], true)) $role = 'MAHASISWA';

        $st = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $st->execute([$username, $email]);
        if ($st->fetch()) Response::error('Username atau email sudah digunakan.', 409);

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("INSERT INTO users (username,password,email,role) VALUES (?,?,?,?)")
                 ->execute([$username, $hash, $email, $role]);

        Response::success(null, 'Registrasi berhasil! Silakan login.', 201);
    }

    /* POST /api/auth/change-password  (requires token) */
    public function changePassword(): void {
        $payload = AuthMiddleware::validate();
        $d       = json_decode(file_get_contents('php://input'), true) ?? [];

        $old  = $d['old_password']     ?? '';
        $new  = $d['new_password']     ?? '';
        $conf = $d['confirm_password'] ?? '';

        if (!$old || !$new || !$conf) Response::error('Semua field wajib diisi.', 422);
        if ($new !== $conf)           Response::error('Konfirmasi password tidak cocok.', 422);
        if (strlen($new) < 6)         Response::error('Password baru minimal 6 karakter.', 422);

        $st = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $st->execute([$payload['id']]);
        $user = $st->fetch();

        if (!password_verify($old, $user['password']))
            Response::error('Password lama tidak sesuai.', 401);

        $this->db->prepare("UPDATE users SET password = ? WHERE id = ?")
                 ->execute([password_hash($new, PASSWORD_BCRYPT), $payload['id']]);

        Response::success(null, 'Password berhasil diubah. Silakan login kembali.');
    }

    /* GET /api/auth/me */
    public function me(): void {
        $payload = AuthMiddleware::validate();
        Response::success($payload);
    }
}
