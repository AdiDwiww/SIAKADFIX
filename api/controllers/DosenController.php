<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DosenController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = max(1, (int)($_GET['limit'] ?? 10));
        $search = trim($_GET['search'] ?? '');
        $offset = ($page - 1) * $limit;

        if ($search) {
            $like = "%$search%";
            $stC  = $this->db->prepare("SELECT COUNT(*) FROM dosen WHERE nip LIKE ? OR nama LIKE ?");
            $stC->execute([$like,$like]);
            $total = (int)$stC->fetchColumn();
            $stD   = $this->db->prepare(
                "SELECT * FROM dosen WHERE nip LIKE ? OR nama LIKE ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
            );
            $stD->execute([$like,$like]);
        } else {
            $total = (int)$this->db->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
            $stD   = $this->db->prepare("SELECT * FROM dosen ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stD->execute();
        }
        Response::paginate($stD->fetchAll(), $total, $page, $limit);
    }

    public function show(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');
        $st = $this->db->prepare("SELECT * FROM dosen WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) Response::error('Dosen tidak ditemukan.', 404);
        Response::success($row);
    }

    public function store(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $nip     = trim($_POST['nip']     ?? '');
        $nama    = trim($_POST['nama']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $alamat  = trim($_POST['alamat']  ?? '');

        if (!$nip || !$nama) Response::error('NIP dan Nama wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM dosen WHERE nip = ?");
        $chk->execute([$nip]);
        if ($chk->fetch()) Response::error('NIP sudah terdaftar.', 409);

        $foto = null;
        if (!empty($_FILES['foto']['name'])) {
            try { $foto = Upload::photo($_FILES['foto'], 'dosen'); }
            catch (RuntimeException $e) { Response::error($e->getMessage(), 422); }
        }

        $this->db->prepare(
            "INSERT INTO dosen (nip,nama,email,telepon,alamat,foto) VALUES (?,?,?,?,?,?)"
        )->execute([$nip,$nama,$email,$telepon,$alamat,$foto]);

        Response::success(['id' => $this->db->lastInsertId()], 'Dosen berhasil ditambahkan.', 201);
    }

    public function update(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT * FROM dosen WHERE id = ?");
        $st->execute([$id]);
        $old = $st->fetch();
        if (!$old) Response::error('Dosen tidak ditemukan.', 404);

        $nip     = trim($_POST['nip']     ?? $old['nip']);
        $nama    = trim($_POST['nama']    ?? $old['nama']);
        $email   = trim($_POST['email']   ?? $old['email']);
        $telepon = trim($_POST['telepon'] ?? $old['telepon']);
        $alamat  = trim($_POST['alamat']  ?? $old['alamat']);

        if (!$nip || !$nama) Response::error('NIP dan Nama wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM dosen WHERE nip = ? AND id != ?");
        $chk->execute([$nip, $id]);
        if ($chk->fetch()) Response::error('NIP sudah digunakan dosen lain.', 409);

        $foto = $old['foto'];
        if (!empty($_FILES['foto']['name'])) {
            try {
                $newFoto = Upload::photo($_FILES['foto'], 'dosen');
                Upload::delete($old['foto']);
                $foto = $newFoto;
            } catch (RuntimeException $e) { Response::error($e->getMessage(), 422); }
        }

        $this->db->prepare(
            "UPDATE dosen SET nip=?,nama=?,email=?,telepon=?,alamat=?,foto=? WHERE id=?"
        )->execute([$nip,$nama,$email,$telepon,$alamat,$foto,$id]);

        Response::success(null, 'Data dosen berhasil diperbarui.');
    }

    public function destroy(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT foto FROM dosen WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) Response::error('Dosen tidak ditemukan.', 404);

        Upload::delete($row['foto']);
        $this->db->prepare("DELETE FROM dosen WHERE id = ?")->execute([$id]);
        Response::success(null, 'Dosen berhasil dihapus.');
    }

    public function bulkDestroy(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $d   = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = array_filter(array_map('intval', $d['ids'] ?? []));
        if (empty($ids)) Response::error('Tidak ada ID yang dipilih.', 422);

        $pl   = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->prepare("SELECT foto FROM dosen WHERE id IN ($pl)");
        $rows->execute(array_values($ids));
        foreach ($rows->fetchAll() as $r) Upload::delete($r['foto']);

        $this->db->prepare("DELETE FROM dosen WHERE id IN ($pl)")->execute(array_values($ids));
        Response::success(null, count($ids) . ' dosen berhasil dihapus.');
    }
}
