<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/ProfileHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class JadwalController {
    private PDO $db;
    private const TAHUN = '2022/2023';
    private const SEMESTER = 'GENAP';

    private const DAY_ORDER = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* GET /api/jadwal */
    public function index(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'MAHASISWA');

        $mhsId = ProfileHelper::getMahasiswaId($this->db, $payload);

        $st = $this->db->prepare(
            "SELECT k.hari, k.jam, k.nama_mk AS nama, k.sks, d.nama AS dosen, k.ruang, k.kelas, k.kode_mk
             FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             LEFT JOIN dosen d ON k.dosen_id = d.id
             WHERE kr.mahasiswa_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
        );
        $st->execute([$mhsId, self::TAHUN, self::SEMESTER]);
        $rows = $st->fetchAll();

        usort($rows, fn($a, $b) => (self::DAY_ORDER[$a['hari']] ?? 99) <=> (self::DAY_ORDER[$b['hari']] ?? 99));

        Response::success([
            'jadwal'         => $rows,
            'tahun_akademik' => self::TAHUN,
            'semester'       => self::SEMESTER,
        ]);
    }

    /* GET /api/jadwal/dosen */
    public function dosen(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'DOSEN');

        $dsnId = ProfileHelper::getDosenId($this->db, $payload);

        $st = $this->db->prepare(
            "SELECT k.id, k.kode_mk, k.hari, k.jam, k.nama_mk AS nama, k.kelas, k.ruang, k.sks
             FROM kuliah k
             WHERE k.dosen_id = ?
             ORDER BY FIELD(k.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), k.jam"
        );
        $st->execute([$dsnId]);
        $rows = $st->fetchAll();

        Response::success([
            'jadwal'         => $rows,
            'tahun_akademik' => self::TAHUN,
            'semester'       => self::SEMESTER,
        ]);
    }
}
