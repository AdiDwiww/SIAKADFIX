<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/GradeHelper.php';
require_once __DIR__ . '/../helpers/ProfileHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class NilaiController {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Detect the latest tahun_akademik + semester_akademik that has KRS
     * entries for the given kuliah_id. Falls back to '2022/2023' / 'GENAP'
     * if no KRS found (shouldn't happen in normal flow).
     */
    private function latestSemester(int $kuliahId): array {
        $st = $this->db->prepare(
            "SELECT tahun_akademik, semester_akademik
             FROM krs
             WHERE kuliah_id = ?
             ORDER BY tahun_akademik DESC,
                      FIELD(semester_akademik,'GENAP','GANJIL') ASC
             LIMIT 1"
        );
        $st->execute([$kuliahId]);
        $row = $st->fetch();
        return $row
            ? [$row['tahun_akademik'], $row['semester_akademik']]
            : ['2022/2023', 'GENAP'];
    }

    /* GET /api/nilai/kelas/{kode_mk} */
    public function byClass(string $kodeMk): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'DOSEN');

        $dsnId = ProfileHelper::getDosenId($this->db, $payload);

        $stMk = $this->db->prepare("SELECT * FROM kuliah WHERE kode_mk = ? AND dosen_id = ?");
        $stMk->execute([$kodeMk, $dsnId]);
        $mk = $stMk->fetch();
        if (!$mk) Response::error('Mata kuliah tidak ditemukan atau bukan kelas Anda.', 404);

        [$tahun, $semester] = $this->latestSemester((int) $mk['id']);

        $st = $this->db->prepare(
            "SELECT m.nim, m.nama, kr.id AS krs_id,
                    n.absen, n.tugas, n.uts, n.uas, n.nilai_akhir, n.nilai_huruf, n.published
             FROM krs kr
             JOIN mahasiswa m ON kr.mahasiswa_id = m.id
             LEFT JOIN nilai n ON n.krs_id = kr.id
             WHERE kr.kuliah_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?
             ORDER BY m.nim"
        );
        $st->execute([$mk['id'], $tahun, $semester]);
        $students = $st->fetchAll();

        foreach ($students as &$s) {
            $s['absen'] = $s['absen'] ?? '';
            $s['tugas'] = $s['tugas'] ?? '';
            $s['uts']   = $s['uts'] ?? '';
            $s['uas']   = $s['uas'] ?? '';
        }

        Response::success([
            'kuliah'          => $mk,
            'tahun_akademik'  => $tahun,
            'semester'        => $semester,
            'students'        => $students,
        ]);
    }

    /* GET /api/nilai/kelas-list */
    public function classList(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'DOSEN');

        $dsnId = ProfileHelper::getDosenId($this->db, $payload);

        $st = $this->db->prepare(
            "SELECT kode_mk, nama_mk, kelas FROM kuliah WHERE dosen_id = ? ORDER BY kode_mk"
        );
        $st->execute([$dsnId]);

        Response::success($st->fetchAll());
    }

    /* PUT /api/nilai */
    public function update(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'DOSEN');

        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $kodeMk = trim($d['kode_mk'] ?? '');
        $nim    = trim($d['nim'] ?? '');
        $field  = trim($d['field'] ?? '');
        $value  = $d['value'] ?? '';

        if (!$kodeMk || !$nim || !$field)
            Response::error('Data tidak lengkap.', 422);

        if (!in_array($field, ['absen', 'tugas', 'uts', 'uas'], true))
            Response::error('Field tidak valid.', 422);

        $dsnId = ProfileHelper::getDosenId($this->db, $payload);

        $stMk = $this->db->prepare("SELECT id FROM kuliah WHERE kode_mk = ? AND dosen_id = ?");
        $stMk->execute([$kodeMk, $dsnId]);
        $mk = $stMk->fetch();
        if (!$mk) Response::error('Mata kuliah tidak ditemukan.', 404);

        [$tahun, $semester] = $this->latestSemester((int) $mk['id']);

        $stKrs = $this->db->prepare(
            "SELECT kr.id AS krs_id FROM krs kr
             JOIN mahasiswa m ON kr.mahasiswa_id = m.id
             WHERE kr.kuliah_id = ? AND m.nim = ?
               AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
        );
        $stKrs->execute([$mk['id'], $nim, $tahun, $semester]);
        $krs = $stKrs->fetch();
        if (!$krs) Response::error('Mahasiswa tidak terdaftar di kelas ini.', 404);

        $krsId = (int) $krs['krs_id'];
        $numVal = ($value === '' || $value === null) ? null : (float) $value;

        $stNilai = $this->db->prepare("SELECT * FROM nilai WHERE krs_id = ?");
        $stNilai->execute([$krsId]);
        $nilai = $stNilai->fetch();

        if ($nilai) {
            $data = [
                'absen' => $nilai['absen'],
                'tugas' => $nilai['tugas'],
                'uts'   => $nilai['uts'],
                'uas'   => $nilai['uas'],
            ];
            $data[$field] = $numVal;

            $calc = GradeHelper::calculate(
                $data['absen'] !== null ? (float) $data['absen'] : null,
                $data['tugas'] !== null ? (float) $data['tugas'] : null,
                $data['uts'] !== null ? (float) $data['uts'] : null,
                $data['uas'] !== null ? (float) $data['uas'] : null
            );

            $this->db->prepare(
                "UPDATE nilai SET absen=?, tugas=?, uts=?, uas=?, nilai_akhir=?, nilai_huruf=?, bobot=? WHERE krs_id=?"
            )->execute([
                $data['absen'], $data['tugas'], $data['uts'], $data['uas'],
                $calc['akhir'], $calc['huruf'], $calc['bobot'], $krsId
            ]);
        } else {
            $data = ['absen' => null, 'tugas' => null, 'uts' => null, 'uas' => null];
            $data[$field] = $numVal;

            $calc = GradeHelper::calculate(
                $data['absen'] !== null ? (float) $data['absen'] : null,
                $data['tugas'] !== null ? (float) $data['tugas'] : null,
                $data['uts'] !== null ? (float) $data['uts'] : null,
                $data['uas'] !== null ? (float) $data['uas'] : null
            );

            $this->db->prepare(
                "INSERT INTO nilai (krs_id, absen, tugas, uts, uas, nilai_akhir, nilai_huruf, bobot)
                 VALUES (?,?,?,?,?,?,?,?)"
            )->execute([
                $krsId, $data['absen'], $data['tugas'], $data['uts'], $data['uas'],
                $calc['akhir'], $calc['huruf'], $calc['bobot']
            ]);
        }

        Response::success([
            'akhir' => $calc['akhir'] ?? '-',
            'huruf' => $calc['huruf'] ?? '-',
        ], 'Nilai berhasil disimpan.');
    }

    /* POST /api/nilai/publish */
    public function publish(): void {
        $payload = AuthMiddleware::validate();
        AuthMiddleware::requireRole($payload, 'DOSEN');

        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $kodeMk = trim($d['kode_mk'] ?? '');
        if (!$kodeMk) Response::error('Kode mata kuliah wajib diisi.', 422);

        $dsnId = ProfileHelper::getDosenId($this->db, $payload);

        $stMk = $this->db->prepare("SELECT id FROM kuliah WHERE kode_mk = ? AND dosen_id = ?");
        $stMk->execute([$kodeMk, $dsnId]);
        $mk = $stMk->fetch();
        if (!$mk) Response::error('Mata kuliah tidak ditemukan.', 404);

        [$tahun, $semester] = $this->latestSemester((int) $mk['id']);

        $this->db->prepare(
            "UPDATE nilai n
             JOIN krs kr ON n.krs_id = kr.id
             SET n.published = 1
             WHERE kr.kuliah_id = ? AND kr.tahun_akademik = ? AND kr.semester_akademik = ?"
        )->execute([$mk['id'], $tahun, $semester]);

        Response::success(null, 'Nilai berhasil dipublikasikan!');
    }
}
