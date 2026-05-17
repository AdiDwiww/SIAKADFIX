<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MahasiswaController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* GET /api/mahasiswa */
    public function index(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, ['ADMIN', 'DOSEN']);
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $limit  = max(1, (int)($_GET['limit']  ?? 10));
        $search = trim($_GET['search'] ?? '');
        $offset = ($page - 1) * $limit;

        if ($search) {
            $like = "%$search%";
            $stC  = $this->db->prepare(
                "SELECT COUNT(*) FROM mahasiswa WHERE nim LIKE ? OR nama LIKE ? OR jurusan LIKE ?"
            );
            $stC->execute([$like, $like, $like]);
            $total = (int)$stC->fetchColumn();

            $stD = $this->db->prepare(
                "SELECT * FROM mahasiswa WHERE nim LIKE ? OR nama LIKE ? OR jurusan LIKE ?
                 ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
            );
            $stD->execute([$like, $like, $like]);
        } else {
            $total = (int)$this->db->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
            $stD   = $this->db->prepare(
                "SELECT * FROM mahasiswa ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
            );
            $stD->execute();
        }

        Response::paginate($stD->fetchAll(), $total, $page, $limit);
    }

    /* GET /api/mahasiswa/{id} */
    public function show(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, ['ADMIN', 'DOSEN']);
        $st = $this->db->prepare("SELECT * FROM mahasiswa WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) Response::error('Mahasiswa tidak ditemukan.', 404);
        Response::success($row);
    }

    /* POST /api/mahasiswa */
    public function store(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $nim     = trim($_POST['nim']     ?? '');
        $nama    = trim($_POST['nama']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $alamat  = trim($_POST['alamat']  ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');

        if (!$nim || !$nama) Response::error('NIM dan Nama wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
        $chk->execute([$nim]);
        if ($chk->fetch()) Response::error('NIM sudah terdaftar.', 409);

        $foto = null;
        if (!empty($_FILES['foto']['name'])) {
            try { $foto = Upload::photo($_FILES['foto'], 'mahasiswa'); }
            catch (RuntimeException $e) { Response::error($e->getMessage(), 422); }
        }

        $this->db->prepare(
            "INSERT INTO mahasiswa (nim,nama,email,telepon,alamat,jurusan,angkatan,foto)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([$nim,$nama,$email,$telepon,$alamat,$jurusan,$angkatan ?: null,$foto]);

        Response::success(['id' => $this->db->lastInsertId()], 'Mahasiswa berhasil ditambahkan.', 201);
    }

    /* POST /api/mahasiswa/{id}  (multipart update) */
    public function update(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT * FROM mahasiswa WHERE id = ?");
        $st->execute([$id]);
        $old = $st->fetch();
        if (!$old) Response::error('Mahasiswa tidak ditemukan.', 404);

        $nim     = trim($_POST['nim']     ?? $old['nim']);
        $nama    = trim($_POST['nama']    ?? $old['nama']);
        $email   = trim($_POST['email']   ?? $old['email']);
        $telepon = trim($_POST['telepon'] ?? $old['telepon']);
        $alamat  = trim($_POST['alamat']  ?? $old['alamat']);
        $jurusan = trim($_POST['jurusan'] ?? $old['jurusan']);
        $angkatan = trim($_POST['angkatan'] ?? $old['angkatan']);

        if (!$nim || !$nama) Response::error('NIM dan Nama wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id != ?");
        $chk->execute([$nim, $id]);
        if ($chk->fetch()) Response::error('NIM sudah digunakan mahasiswa lain.', 409);

        $foto = $old['foto'];
        if (!empty($_FILES['foto']['name'])) {
            try {
                $newFoto = Upload::photo($_FILES['foto'], 'mahasiswa');
                Upload::delete($old['foto']);
                $foto = $newFoto;
            } catch (RuntimeException $e) { Response::error($e->getMessage(), 422); }
        }

        $this->db->prepare(
            "UPDATE mahasiswa SET nim=?,nama=?,email=?,telepon=?,alamat=?,jurusan=?,angkatan=?,foto=? WHERE id=?"
        )->execute([$nim,$nama,$email,$telepon,$alamat,$jurusan,$angkatan ?: null,$foto,$id]);

        Response::success(null, 'Data mahasiswa berhasil diperbarui.');
    }

    /* DELETE /api/mahasiswa/{id} */
    public function destroy(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT foto FROM mahasiswa WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) Response::error('Mahasiswa tidak ditemukan.', 404);

        Upload::delete($row['foto']);
        $this->db->prepare("DELETE FROM mahasiswa WHERE id = ?")->execute([$id]);
        Response::success(null, 'Mahasiswa berhasil dihapus.');
    }

    /* DELETE /api/mahasiswa/bulk-delete */
    public function bulkDestroy(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $d   = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = array_filter(array_map('intval', $d['ids'] ?? []));
        if (empty($ids)) Response::error('Tidak ada ID yang dipilih.', 422);

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->prepare("SELECT foto FROM mahasiswa WHERE id IN ($placeholders)");
        $rows->execute(array_values($ids));
        foreach ($rows->fetchAll() as $r) Upload::delete($r['foto']);

        $this->db->prepare("DELETE FROM mahasiswa WHERE id IN ($placeholders)")->execute(array_values($ids));
        Response::success(null, count($ids) . ' mahasiswa berhasil dihapus.');
    }
}
