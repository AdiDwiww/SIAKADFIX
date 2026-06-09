<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/GradeHelper.php';
require_once __DIR__ . '/../helpers/ProfileHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class KhsController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* GET /api/khs */
    public function index(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'MAHASISWA');

        $mhsId = ProfileHelper::getMahasiswaId($this->db, $payload);
        $profile = ProfileHelper::getMahasiswaProfile($this->db, $payload);

        $semester = $_GET['semester'] ?? 'all';

        $query = "SELECT k.kode_mk AS kode, k.nama_mk AS nama, k.sks,
                         d.nama AS dosen, n.absen, n.tugas, n.uts, n.uas,
                         n.nilai_akhir, n.nilai_huruf, n.bobot, n.published
                  FROM krs kr
                  JOIN kuliah k ON kr.kuliah_id = k.id
                  LEFT JOIN dosen d ON k.dosen_id = d.id
                  LEFT JOIN nilai n ON n.krs_id = kr.id
                  WHERE kr.mahasiswa_id = ?";

        $params = [$mhsId];

        if ($semester !== 'all') {
            $query .= " AND k.semester = ?";
            $params[] = $semester;
        }

        $query .= " ORDER BY k.kode_mk";

        $st = $this->db->prepare($query);
        $st->execute($params);
        $rows = $st->fetchAll();

        $nilai = [];
        $totalSks = 0;
        $totalMutu = 0;

        foreach ($rows as $row) {
            $huruf = $row['nilai_huruf'];
            $bobot = $row['bobot'] !== null ? (float) $row['bobot'] : null;

            if (!$huruf && $row['absen'] !== null) {
                $calc = GradeHelper::calculate(
                    (float) $row['absen'], (float) $row['tugas'],
                    (float) $row['uts'], (float) $row['uas']
                );
                $huruf = $calc['huruf'];
                $bobot = $calc['bobot'];
            }

            $item = [
                'kode'        => $row['kode'],
                'nama'        => $row['nama'],
                'sks'         => (int) $row['sks'],
                'dosen'       => $row['dosen'],
                'nilai_huruf' => $huruf ?? '-',
                'bobot'       => $bobot ?? 0,
            ];

            if ($huruf && $huruf !== '-') {
                $totalSks += (int) $row['sks'];
                $totalMutu += (int) $row['sks'] * $bobot;
            }

            $nilai[] = $item;
        }

        $ips = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0;

        // Hitung IPK & SKS Kumulatif keseluruhan (Independen dari filter semester)
        $stIpk = $this->db->prepare(
            "SELECT k.sks, n.bobot, n.nilai_huruf, n.absen, n.tugas, n.uts, n.uas
             FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             JOIN nilai n ON n.krs_id = kr.id
             WHERE kr.mahasiswa_id = ?"
        );
        $stIpk->execute([$mhsId]);
        $allNilai = $stIpk->fetchAll();

        $totSksAll = 0;
        $totMutuAll = 0;
        $sksLulusAll = 0;

        foreach ($allNilai as $rn) {
            $huruf = $rn['nilai_huruf'];
            $bobot = $rn['bobot'] !== null ? (float) $rn['bobot'] : null;

            if (!$huruf && $rn['absen'] !== null) {
                $calc = GradeHelper::calculate(
                    (float) $rn['absen'], (float) $rn['tugas'],
                    (float) $rn['uts'], (float) $rn['uas']
                );
                $huruf = $calc['huruf'];
                $bobot = $calc['bobot'];
            }

            if ($huruf && $huruf !== '-') {
                $totSksAll += (int) $rn['sks'];
                $totMutuAll += (int) $rn['sks'] * $bobot;
                if (!in_array($huruf, ['D', 'E'])) {
                    $sksLulusAll += (int) $rn['sks'];
                }
            }
        }

        $ipk = $totSksAll > 0 ? round($totMutuAll / $totSksAll, 2) : 0;

        Response::success([
            'profile'        => $profile,
            'nilai'          => $nilai,
            'ips'            => $ips,
            'ipk'            => $ipk,
            'total_sks'      => $totalSks, // SKS semester ini
            'sks_lulus'      => $sksLulusAll,
            'total_sks_all'  => $totSksAll, // SKS diambil keseluruhan
            'semester_aktif' => $semester
        ]);
    }
}
