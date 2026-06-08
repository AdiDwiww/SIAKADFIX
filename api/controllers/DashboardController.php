<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DashboardController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* GET /api/dashboard */
    public function index(): void {
        $payload = AuthMiddleware::validate();

        $totalMhs  = (int) $this->db->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
        $totalDsn  = (int) $this->db->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
        $totalMk   = (int) $this->db->query("SELECT COUNT(*) FROM kuliah")->fetchColumn();

        $latestMhs = $this->db->query(
            "SELECT nim, nama, jurusan, foto FROM mahasiswa ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        $latestDsn = $this->db->query(
            "SELECT nip, nama, email, foto FROM dosen ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        $latestMk  = $this->db->query(
            "SELECT k.kode_mk, k.nama_mk, k.sks, d.nama AS nama_dosen
             FROM kuliah k LEFT JOIN dosen d ON k.dosen_id = d.id
             ORDER BY k.created_at DESC LIMIT 5"
        )->fetchAll();

        // ─── Analytics: Jurusan Distribution ───────────────────
        $jurusanDist = $this->db->query(
            "SELECT COALESCE(jurusan, 'Belum Ditentukan') AS jurusan, COUNT(*) AS count
             FROM mahasiswa GROUP BY jurusan ORDER BY count DESC"
        )->fetchAll();

        // ─── Analytics: Semester Distribution ──────────────────
        $semesterDist = $this->db->query(
            "SELECT semester, COUNT(*) AS count
             FROM kuliah WHERE semester IS NOT NULL
             GROUP BY semester ORDER BY semester ASC"
        )->fetchAll();

        // ─── Analytics: SKS Totals ─────────────────────────────
        $sksData = $this->db->query(
            "SELECT COALESCE(SUM(sks), 0) AS total_sks, ROUND(COALESCE(AVG(sks), 0), 1) AS avg_sks FROM kuliah"
        )->fetch();

        // ─── Analytics: Angkatan Distribution ──────────────────
        $angkatanDist = $this->db->query(
            "SELECT COALESCE(angkatan, 'N/A') AS angkatan, COUNT(*) AS count
             FROM mahasiswa GROUP BY angkatan ORDER BY angkatan DESC"
        )->fetchAll();

        Response::success([
            'role'  => $payload['role'],
            'stats' => [
                'total_mahasiswa' => $totalMhs,
                'total_dosen'     => $totalDsn,
                'total_kuliah'    => $totalMk,
            ],
            'latest_mahasiswa' => $latestMhs,
            'latest_dosen'     => $latestDsn,
            'latest_kuliah'    => $latestMk,
            // New analytics data
            'jurusan_distribution'  => $jurusanDist,
            'semester_distribution' => $semesterDist,
            'angkatan_distribution' => $angkatanDist,
            'total_sks'             => (int) $sksData['total_sks'],
            'avg_sks'               => (float) $sksData['avg_sks'],
        ]);
    }
}
