<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/ProfileHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class KrsController {
    private PDO $db;
    private const TAHUN    = '2022/2023';
    private const SEMESTER = 'GENAP';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Deteksi semester mahasiswa saat ini berdasarkan
     * semester kuliah tertinggi yang ada di KRS mereka.
     * Default = 1 jika belum punya KRS sama sekali.
     */
    private function detectSemester(int $mhsId): int {
        $st = $this->db->prepare(
            "SELECT MAX(k.semester) AS max_sem
             FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             WHERE kr.mahasiswa_id = ? AND k.semester IS NOT NULL"
        );
        $st->execute([$mhsId]);
        $row = $st->fetch();
        return (int)($row['max_sem'] ?? 1);
    }

    /* GET /api/krs */
    public function index(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'MAHASISWA');

        $mhsId  = ProfileHelper::getMahasiswaId($this->db, $payload);
        $profile = ProfileHelper::getMahasiswaProfile($this->db, $payload);

        // Ambil semester filter dari query param, atau auto-detect dari KRS mahasiswa
        $reqSem = isset($_GET['semester']) && is_numeric($_GET['semester'])
            ? (int)$_GET['semester']
            : $this->detectSemester($mhsId);

        // Clamp 1-8
        $reqSem = max(1, min(8, $reqSem));

        // Ambil semua MK untuk semester yang diminta
        $st = $this->db->prepare(
            "SELECT k.*, d.nama AS nama_dosen,
                    CONCAT(k.hari, ', ', k.jam, ' (', k.ruang, ')') AS jadwal
             FROM kuliah k
             LEFT JOIN dosen d ON k.dosen_id = d.id
             WHERE k.semester = ?
             ORDER BY k.kode_mk"
        );
        $st->execute([$reqSem]);
        $available = $st->fetchAll();

        // Ambil MK yang sudah terdaftar di KRS semester ini
        $stSel = $this->db->prepare(
            "SELECT k.kode_mk FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             WHERE kr.mahasiswa_id = ? AND k.semester = ?"
        );
        $stSel->execute([$mhsId, $reqSem]);
        $selected = array_column($stSel->fetchAll(), 'kode_mk');

        // Status KRS
        $stStatus = $this->db->prepare(
            "SELECT kr.status FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             WHERE kr.mahasiswa_id = ? AND k.semester = ?
             LIMIT 1"
        );
        $stStatus->execute([$mhsId, $reqSem]);
        $statusRow = $stStatus->fetch();
        $status = $statusRow ? $statusRow['status'] : 'DRAFT';

        Response::success([
            'profile'        => $profile,
            'available'      => $available,
            'selected'       => $selected,
            'status'         => $status,
            'semester_aktif' => $reqSem,
            'tahun_akademik' => self::TAHUN,
            'semester'       => self::SEMESTER,
        ]);
    }

    /* POST /api/krs */
    public function store(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'MAHASISWA');

        $d     = json_decode(file_get_contents('php://input'), true) ?? [];
        $codes = $d['kode_mk'] ?? [];
        if (!is_array($codes)) Response::error('Format data tidak valid.', 422);

        $mhsId = ProfileHelper::getMahasiswaId($this->db, $payload);

        $totalSks  = 0;
        $kuliahIds = [];
        $semCheck  = null;
        foreach ($codes as $code) {
            $st = $this->db->prepare("SELECT id, sks, semester FROM kuliah WHERE kode_mk = ?");
            $st->execute([trim($code)]);
            $mk = $st->fetch();
            if (!$mk) Response::error("Mata kuliah $code tidak ditemukan.", 404);
            // Pastikan semua MK dari semester yang sama
            if ($semCheck === null) $semCheck = (int)$mk['semester'];
            if ((int)$mk['semester'] !== $semCheck)
                Response::error('Semua mata kuliah harus dari semester yang sama.', 422);
            $totalSks += (int) $mk['sks'];
            $kuliahIds[] = (int) $mk['id'];
        }

        if ($totalSks > 24) Response::error('Total SKS melebihi batas maksimal 24 SKS.', 422);
        if (count($codes) === 0) Response::error('Pilih minimal 1 mata kuliah.', 422);

        // Tentukan tahun & semester akademik berdasarkan semester MK
        $semNo   = $semCheck ?? 1;
        $isGanjil = ($semNo % 2 === 1);
        $tahun   = self::TAHUN;
        $semAkd  = $isGanjil ? 'GANJIL' : 'GENAP';

        $this->db->beginTransaction();
        try {
            // Hapus KRS lama untuk semester MK ini
            $del = $this->db->prepare(
                "DELETE kr FROM krs kr
                 JOIN kuliah k ON kr.kuliah_id = k.id
                 WHERE kr.mahasiswa_id = ? AND k.semester = ?"
            );
            $del->execute([$mhsId, $semNo]);

            $ins = $this->db->prepare(
                "INSERT INTO krs (mahasiswa_id, kuliah_id, tahun_akademik, semester_akademik, status)
                 VALUES (?, ?, ?, ?, 'DISETUJUI')"
            );
            foreach ($kuliahIds as $kid) {
                $ins->execute([$mhsId, $kid, $tahun, $semAkd]);
            }

            $this->db->commit();
            Response::success(['total_sks' => $totalSks], 'KRS berhasil disimpan.');
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Gagal menyimpan KRS: ' . $e->getMessage(), 500);
        }
    }
}
