<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class KuliahController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index(): void {
        AuthMiddleware::validate();
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = max(1, (int)($_GET['limit'] ?? 10));
        $search = trim($_GET['search'] ?? '');
        $offset = ($page - 1) * $limit;

        $baseQ  = "FROM kuliah k LEFT JOIN dosen d ON k.dosen_id = d.id";
        $selQ   = "SELECT k.*, d.nama AS nama_dosen $baseQ";

        if ($search) {
            $like  = "%$search%";
            $stC   = $this->db->prepare("SELECT COUNT(*) $baseQ WHERE k.kode_mk LIKE ? OR k.nama_mk LIKE ?");
            $stC->execute([$like,$like]);
            $total = (int)$stC->fetchColumn();
            $stD   = $this->db->prepare("$selQ WHERE k.kode_mk LIKE ? OR k.nama_mk LIKE ? ORDER BY k.created_at DESC LIMIT $limit OFFSET $offset");
            $stD->execute([$like,$like]);
        } else {
            $total = (int)$this->db->query("SELECT COUNT(*) FROM kuliah")->fetchColumn();
            $stD   = $this->db->prepare("$selQ ORDER BY k.created_at DESC LIMIT $limit OFFSET $offset");
            $stD->execute();
        }
        Response::paginate($stD->fetchAll(), $total, $page, $limit);
    }

    public function show(string $id): void {
        AuthMiddleware::validate();
        $st = $this->db->prepare(
            "SELECT k.*, d.nama AS nama_dosen FROM kuliah k LEFT JOIN dosen d ON k.dosen_id = d.id WHERE k.id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) Response::error('Mata kuliah tidak ditemukan.', 404);
        Response::success($row);
    }

    public function store(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $d       = json_decode(file_get_contents('php://input'), true) ?? [];
        $kode    = trim($d['kode_mk']  ?? '');
        $nama    = trim($d['nama_mk']  ?? '');
        $sks     = (int)($d['sks']     ?? 2);
        $sem     = (int)($d['semester']  ?? 0);
        $dosenId = $d['dosen_id'] ? (int)$d['dosen_id'] : null;

        if (!$kode || !$nama) Response::error('Kode MK dan Nama MK wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM kuliah WHERE kode_mk = ?");
        $chk->execute([$kode]);
        if ($chk->fetch()) Response::error('Kode MK sudah terdaftar.', 409);

        $this->db->prepare(
            "INSERT INTO kuliah (kode_mk,nama_mk,sks,semester,dosen_id) VALUES (?,?,?,?,?)"
        )->execute([$kode,$nama,$sks,$sem ?: null,$dosenId]);

        Response::success(['id' => $this->db->lastInsertId()], 'Mata kuliah berhasil ditambahkan.', 201);
    }

    public function update(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT * FROM kuliah WHERE id = ?");
        $st->execute([$id]);
        if (!$st->fetch()) Response::error('Mata kuliah tidak ditemukan.', 404);

        $d       = json_decode(file_get_contents('php://input'), true) ?? [];
        $kode    = trim($d['kode_mk']  ?? '');
        $nama    = trim($d['nama_mk']  ?? '');
        $sks     = (int)($d['sks']     ?? 2);
        $sem     = (int)($d['semester']  ?? 0);
        $dosenId = isset($d['dosen_id']) && $d['dosen_id'] ? (int)$d['dosen_id'] : null;

        if (!$kode || !$nama) Response::error('Kode MK dan Nama MK wajib diisi.', 422);

        $chk = $this->db->prepare("SELECT id FROM kuliah WHERE kode_mk = ? AND id != ?");
        $chk->execute([$kode, $id]);
        if ($chk->fetch()) Response::error('Kode MK sudah digunakan.', 409);

        $this->db->prepare(
            "UPDATE kuliah SET kode_mk=?,nama_mk=?,sks=?,semester=?,dosen_id=? WHERE id=?"
        )->execute([$kode,$nama,$sks,$sem ?: null,$dosenId,$id]);

        Response::success(null, 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(string $id): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $st = $this->db->prepare("SELECT id FROM kuliah WHERE id = ?");
        $st->execute([$id]);
        if (!$st->fetch()) Response::error('Mata kuliah tidak ditemukan.', 404);

        $this->db->prepare("DELETE FROM kuliah WHERE id = ?")->execute([$id]);
        Response::success(null, 'Mata kuliah berhasil dihapus.');
    }

    public function getDosenList(): void {
        AuthMiddleware::validate();
        $rows = $this->db->query("SELECT id, nama, nip FROM dosen ORDER BY nama")->fetchAll();
        Response::success($rows);
    }
}
