USE uas_psi;
-- Insert nilai untuk semua KRS yang belum punya nilai (id >= 337)
INSERT INTO nilai(krs_id,absen,tugas,uts,uas,nilai_akhir,nilai_huruf,bobot,published)
SELECT k.id,
  IF((k.id - 337) % 2 = 0, 100, 85),
  IF((k.id - 337) % 2 = 0, 88, 78),
  IF((k.id - 337) % 2 = 0, 85, 75),
  IF((k.id - 337) % 2 = 0, 90, 78),
  IF((k.id - 337) % 2 = 0, 89.10, 77.80),
  IF((k.id - 337) % 2 = 0, 'A', 'B'),
  IF((k.id - 337) % 2 = 0, 4.00, 3.00),
  1
FROM krs k
WHERE k.id >= 337
ON DUPLICATE KEY UPDATE nilai_huruf=VALUES(nilai_huruf), bobot=VALUES(bobot), published=1;

SELECT COUNT(*) as total_nilai FROM nilai;
SELECT kr.id, n.nilai_huruf FROM krs kr JOIN nilai n ON n.krs_id=kr.id WHERE kr.mahasiswa_id=1 AND kr.tahun_akademik='2022/2023' AND kr.semester_akademik='GENAP';
