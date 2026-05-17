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
        ]);
    }
}
