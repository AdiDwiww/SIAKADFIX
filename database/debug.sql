USE uas_psi;
-- Cek krs_id mhs1 TI404 sekarang
SELECT kr.id, k.kode_mk, k.nama_mk, n.nilai_huruf FROM krs kr
JOIN kuliah k ON kr.kuliah_id=k.id
LEFT JOIN nilai n ON n.krs_id=kr.id
WHERE kr.mahasiswa_id=1 AND kr.tahun_akademik='2022/2023' AND kr.semester_akademik='GENAP';

-- Total KRS dan nilai
SELECT COUNT(*) as total_krs FROM krs;
SELECT COUNT(*) as total_nilai FROM nilai;

-- Cek ips_history mhs1
SELECT semester, ips FROM ips_history WHERE mahasiswa_id=1 ORDER BY semester;

-- Cek TI404 punya berapa mahasiswa di current sem
SELECT COUNT(*) as mhs_in_TI404 FROM krs WHERE kuliah_id=25 AND tahun_akademik='2022/2023' AND semester_akademik='GENAP';
