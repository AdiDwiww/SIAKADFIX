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
        $page     = max(1, (int)($_GET['page']  ?? 1));
        $limit    = max(1, (int)($_GET['limit'] ?? 10));
        $search   = trim($_GET['search'] ?? '');
        $semester = isset($_GET['semester']) && $_GET['semester'] !== '' ? (int)$_GET['semester'] : 0;
        $offset   = ($page - 1) * $limit;

        $baseQ  = "FROM kuliah k LEFT JOIN dosen d ON k.dosen_id = d.id";
        $selQ   = "SELECT k.*, d.nama AS nama_dosen $baseQ";

        $whereCols = [];
        $params    = [];

        if ($search) {
            $whereCols[] = "(k.kode_mk LIKE ? OR k.nama_mk LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($semester > 0) {
            $whereCols[] = "k.semester = ?";
            $params[] = $semester;
        }

        $whereClause = !empty($whereCols) ? "WHERE " . implode(" AND ", $whereCols) : "";

        $stC   = $this->db->prepare("SELECT COUNT(*) $baseQ $whereClause");
        $stC->execute($params);
        $total = (int)$stC->fetchColumn();

        $stD   = $this->db->prepare("$selQ $whereClause ORDER BY k.created_at DESC LIMIT $limit OFFSET $offset");
        $stD->execute($params);
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

    public function import(): void {
        $p = AuthMiddleware::validate();
        AuthMiddleware::requireRole($p, 'ADMIN');

        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $items = $d['items'] ?? [];
        if (!is_array($items)) Response::error('Format import tidak valid.', 422);

        $dosenRows = $this->db->query("SELECT id, nip FROM dosen")->fetchAll();
        $dosenById = [];
        $dosenByNip = [];
        foreach ($dosenRows as $row) {
            $dosenById[(int)$row['id']] = (int)$row['id'];
            $dosenByNip[trim($row['nip'])] = (int)$row['id'];
        }

        $result = ['imported' => 0, 'skipped' => 0, 'errors' => []];
        $insertStmt = $this->db->prepare(
            "INSERT INTO kuliah (kode_mk,nama_mk,sks,semester,dosen_id) VALUES (?,?,?,?,?)"
        );

        foreach ($items as $index => $item) {
            $rowNumber = $index + 1;
            $kode = trim($item['kode_mk'] ?? $item['kode'] ?? '');
            $nama = trim($item['nama_mk'] ?? $item['nama'] ?? '');
            $sks  = (int)($item['sks'] ?? 2);
            $sem  = (int)($item['semester'] ?? 0);
            $dosenRef = trim((string)($item['dosen_id'] ?? $item['dosen_nip'] ?? $item['dosen'] ?? ''));

            if (!$kode || !$nama) {
                $result['errors'][] = "Baris {$rowNumber}: Kode MK dan Nama MK wajib diisi.";
                continue;
            }

            $chk = $this->db->prepare("SELECT id FROM kuliah WHERE kode_mk = ?");
            $chk->execute([$kode]);
            if ($chk->fetch()) {
                $result['skipped']++;
                continue;
            }

            $dosenId = null;
            if ($dosenRef !== '') {
                if (is_numeric($dosenRef) && isset($dosenById[(int)$dosenRef])) {
                    $dosenId = (int)$dosenRef;
                } elseif (isset($dosenByNip[$dosenRef])) {
                    $dosenId = $dosenByNip[$dosenRef];
                }
            }

            try {
                $insertStmt->execute([$kode, $nama, $sks ?: 2, $sem ?: null, $dosenId]);
                $result['imported']++;
            } catch (Throwable $e) {
                $result['errors'][] = "Baris {$rowNumber}: Gagal menambahkan mata kuliah ({$e->getMessage()}).";
            }
        }

        Response::success($result, 'Import mata kuliah selesai.');
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
