<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/ProfileHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DashboardController {
    private PDO $db;
    private const TAHUN = '2022/2023';
    private const SEMESTER = 'GENAP';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /* GET /api/dashboard */
    public function index(): void {
        $payload = AuthMiddleware::validate();

        match ($payload['role']) {
            'MAHASISWA' => $this->mahasiswaDashboard($payload),
            'DOSEN'     => $this->dosenDashboard($payload),
            default     => $this->adminDashboard($payload),
        };
    }

    private function adminDashboard(array $payload): void {
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

        $jurusanDist = $this->db->query(
            "SELECT COALESCE(jurusan, 'Belum Ditentukan') AS jurusan, COUNT(*) AS count
             FROM mahasiswa GROUP BY jurusan ORDER BY count DESC"
        )->fetchAll();

        $semesterDist = $this->db->query(
            "SELECT semester, COUNT(*) AS count
             FROM kuliah WHERE semester IS NOT NULL
             GROUP BY semester ORDER BY semester ASC"
        )->fetchAll();

        $sksData = $this->db->query(
            "SELECT COALESCE(SUM(sks), 0) AS total_sks, ROUND(COALESCE(AVG(sks), 0), 1) AS avg_sks FROM kuliah"
        )->fetch();

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
            'latest_mahasiswa'      => $latestMhs,
            'latest_dosen'          => $latestDsn,
            'latest_kuliah'         => $latestMk,
            'jurusan_distribution'  => $jurusanDist,
            'semester_distribution' => $semesterDist,
            'angkatan_distribution' => $angkatanDist,
            'total_sks'             => (int) $sksData['total_sks'],
            'avg_sks'               => (float) $sksData['avg_sks'],
        ]);
    }

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

    private function mahasiswaDashboard(array $payload): void {
        $mhsId = ProfileHelper::getMahasiswaId($this->db, $payload);
        $profile = ProfileHelper::getMahasiswaProfile($this->db, $payload);

        $currentSem = $this->detectSemester($mhsId);

        // Ambil data untuk mk_aktif (Berdasarkan semester aktif saat ini)
        $stKrs = $this->db->prepare(
            "SELECT COUNT(*) FROM krs kr JOIN kuliah k ON kr.kuliah_id = k.id
             WHERE kr.mahasiswa_id = ? AND k.semester = ?"
        );
        $stKrs->execute([$mhsId, $currentSem]);
        $mkCount = (int) $stKrs->fetchColumn();

        // Hitung nilai, IPS per semester (history), IPK, SKS Lulus
        $stNilai = $this->db->prepare(
            "SELECT k.semester, k.sks, n.bobot, n.nilai_huruf, n.absen, n.tugas, n.uts, n.uas
             FROM krs kr
             JOIN kuliah k ON kr.kuliah_id = k.id
             LEFT JOIN nilai n ON n.krs_id = kr.id
             WHERE kr.mahasiswa_id = ?
             ORDER BY k.semester"
        );
        $stNilai->execute([$mhsId]);
        $allNilai = $stNilai->fetchAll();

        $historyData = [];
        $totSksAll = 0;
        $totMutuAll = 0;
        $sksLulus = 0;

        foreach ($allNilai as $rn) {
            $sem = (int) $rn['semester'];
            if (!isset($historyData[$sem])) {
                $historyData[$sem] = ['sks' => 0, 'mutu' => 0];
            }

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
                $mutu = (int) $rn['sks'] * $bobot;
                $historyData[$sem]['sks'] += (int) $rn['sks'];
                $historyData[$sem]['mutu'] += $mutu;

                $totSksAll += (int) $rn['sks'];
                $totMutuAll += $mutu;

                if (!in_array($huruf, ['D', 'E'])) {
                    $sksLulus += (int) $rn['sks'];
                }
            }
        }

        $ipk = $totSksAll > 0 ? round($totMutuAll / $totSksAll, 2) : 0;
        
        $history = [];
        foreach ($historyData as $sem => $data) {
            $ipsSem = $data['sks'] > 0 ? round($data['mutu'] / $data['sks'], 2) : 0;
            $history[] = ['semester' => $sem, 'ips' => $ipsSem];
        }

        // Cari IPS semester terbaru
        $ips = 0;
        if (isset($historyData[$currentSem]) && $historyData[$currentSem]['sks'] > 0) {
            $ips = round($historyData[$currentSem]['mutu'] / $historyData[$currentSem]['sks'], 2);
        } elseif (!empty($history)) {
            $last = end($history);
            $ips = $last['ips'];
        }

        // Jadwal: hari ini dulu, lalu hari-hari berikutnya (wrap week)
        $hariIni = date('l'); // English day name
        $hariMap = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                    'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
        $hariIndonesia = $hariMap[$hariIni] ?? 'Senin';
        $urutan = ['Senin','Selasa','Rabu','Kamis','Jumat'];
        $start  = array_search($hariIndonesia, $urutan);
        if ($start === false) $start = 0;
        $ordered = array_merge(array_slice($urutan, $start), array_slice($urutan, 0, $start));
        $placeholders = implode(',', array_map(fn($h) => "'$h'", $ordered));

        $stJadwal = $this->db->prepare(
            "SELECT k.nama_mk AS nama, k.hari, k.jam, k.ruang
             FROM krs kr JOIN kuliah k ON kr.kuliah_id = k.id
             WHERE kr.mahasiswa_id = ? AND k.semester = ?
             ORDER BY FIELD(k.hari,$placeholders), k.jam LIMIT 3"
        );
        $stJadwal->execute([$mhsId, $currentSem]);
        $jadwal = $stJadwal->fetchAll();

        Response::success([
            'role'        => 'MAHASISWA',
            'profile'     => $profile,
            'ipk'         => $ipk,
            'ips'         => $ips,
            'sks_lulus'   => $sksLulus,
            'mk_aktif'    => $mkCount,
            'ips_history' => $history,
            'jadwal'      => $jadwal,
        ]);
    }

    private function dosenDashboard(array $payload): void {
        $dsnId = ProfileHelper::getDosenId($this->db, $payload);
        $profile = ProfileHelper::getDosenProfile($this->db, $payload);

        $stMk = $this->db->prepare("SELECT * FROM kuliah WHERE dosen_id = ?");
        $stMk->execute([$dsnId]);
        $mkList = $stMk->fetchAll();

        $totalSks = array_sum(array_column($mkList, 'sks'));
        $studentSet = [];

        $nilaiStatus = [];
        foreach ($mkList as $mk) {
            // Auto-detect semester terbaru yang punya KRS untuk kuliah ini
            $stSem = $this->db->prepare(
                "SELECT tahun_akademik, semester_akademik FROM krs
                 WHERE kuliah_id = ?
                 ORDER BY tahun_akademik DESC,
                          FIELD(semester_akademik,'GENAP','GANJIL') ASC
                 LIMIT 1"
            );
            $stSem->execute([$mk['id']]);
            $sem = $stSem->fetch();
            if (!$sem) {
                $nilaiStatus[] = [
                    'kode_mk'   => $mk['kode_mk'],
                    'nama_mk'   => $mk['nama_mk'],
                    'kelas'     => $mk['kelas'],
                    'status'    => 'Belum Ada KRS',
                    'mhs_count' => 0,
                ];
                continue;
            }
            $tahun   = $sem['tahun_akademik'];
            $semName = $sem['semester_akademik'];

            $stMhs = $this->db->prepare(
                "SELECT COUNT(DISTINCT kr.mahasiswa_id) AS cnt
                 FROM krs kr
                 WHERE kr.kuliah_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
            );
            $stMhs->execute([$mk['id'], $tahun, $semName]);
            $cnt = (int) $stMhs->fetchColumn();

            $stNilai = $this->db->prepare(
                "SELECT COUNT(*) AS filled, COUNT(n.nilai_huruf) AS complete
                 FROM krs kr LEFT JOIN nilai n ON n.krs_id = kr.id
                 WHERE kr.kuliah_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
            );
            $stNilai->execute([$mk['id'], $tahun, $semName]);
            $nilaiInfo = $stNilai->fetch();

            $stNims = $this->db->prepare(
                "SELECT m.nim FROM krs kr JOIN mahasiswa m ON kr.mahasiswa_id = m.id
                 WHERE kr.kuliah_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
            );
            $stNims->execute([$mk['id'], $tahun, $semName]);
            foreach ($stNims->fetchAll() as $n) $studentSet[$n['nim']] = true;

            $status = ($nilaiInfo['complete'] >= $cnt && $cnt > 0) ? 'Lengkap' : 'Belum Lengkap';
            $nilaiStatus[] = [
                'kode_mk'   => $mk['kode_mk'],
                'nama_mk'   => $mk['nama_mk'],
                'kelas'     => $mk['kelas'],
                'status'    => $status,
                'mhs_count' => $cnt,
            ];
        }

        $hariIni = date('l');
        $hariMap = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                    'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
        $hariIndonesia = $hariMap[$hariIni] ?? 'Senin';

        $stJadwal = $this->db->prepare(
            "SELECT nama_mk AS nama, kelas, hari, jam, ruang
             FROM kuliah WHERE dosen_id = ? AND hari = ?
             ORDER BY jam"
        );
        $stJadwal->execute([$dsnId, $hariIndonesia]);
        $jadwal = $stJadwal->fetchAll();

        Response::success([
            'role'         => 'DOSEN',
            'profile'      => $profile,
            'mk_count'     => count($mkList),
            'mhs_count'    => count($studentSet),
            'sks_beban'    => $totalSks,
            'jadwal'       => $jadwal,
            'nilai_status' => $nilaiStatus,
        ]);
    }
}
