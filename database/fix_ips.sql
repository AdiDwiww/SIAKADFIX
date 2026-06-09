USE uas_psi;

-- 1. UPDATE ips_history dengan nilai BERVARIASI per mahasiswa per semester
DELETE FROM ips_history;

INSERT INTO ips_history(mahasiswa_id,semester,ips,tahun_akademik)VALUES
-- mhs1 Andi: naik progresif 3.00→3.29→3.57
(1,1,3.00,'2021/2022'),(1,2,3.29,'2021/2022'),(1,3,3.57,'2022/2023'),
-- mhs2 Budi: stabil naik 2.71→2.86→3.00
(2,1,2.71,'2021/2022'),(2,2,2.86,'2021/2022'),(2,3,3.00,'2022/2023'),
-- mhs3 Citra: tinggi stabil 3.71→3.86→4.00
(3,1,3.71,'2021/2022'),(3,2,3.86,'2021/2022'),(3,3,4.00,'2022/2023'),
-- mhs4 Dedi: naik perlahan 2.00→2.29→2.57
(4,1,2.00,'2021/2022'),(4,2,2.29,'2021/2022'),(4,3,2.57,'2022/2023'),
-- mhs5 Eka: naik konsisten 2.86→3.14→3.57
(5,1,2.86,'2021/2022'),(5,2,3.14,'2021/2022'),(5,3,3.57,'2022/2023'),
-- mhs6-10 (angkatan 2022, sem1 history saja)
(6,1,3.00,'2022/2023'),
(7,1,3.71,'2022/2023'),
(8,1,2.71,'2022/2023'),
(9,1,3.29,'2022/2023'),
(10,1,2.14,'2022/2023'),
-- mhs11 Kartika: konsisten tinggi 3.86→3.93→4.00
(11,1,3.86,'2021/2022'),(11,2,3.93,'2021/2022'),(11,3,4.00,'2022/2023'),
-- mhs12 Luki: stabil 2.86→2.93→3.00
(12,1,2.86,'2021/2022'),(12,2,2.93,'2021/2022'),(12,3,3.00,'2022/2023'),
-- mhs13 Maya: naik 3.00→3.29→3.57
(13,1,3.00,'2021/2022'),(13,2,3.29,'2021/2022'),(13,3,3.57,'2022/2023'),
-- mhs14 Nanda: berjuang 1.86→2.14→2.57
(14,1,1.86,'2021/2022'),(14,2,2.14,'2021/2022'),(14,3,2.57,'2022/2023'),
-- mhs15 Olivia: sem1 history
(15,1,3.14,'2022/2023');

-- 2. INSERT nilai untuk KRS current semester (krs_id 337-420 = sem4 mhs1-5,11-14 + sem2 mhs6-10,15)
-- Cek krs_id current dengan query:
-- mhs1 sem4: krs 337-343 | mhs2: 344-350 | mhs3: 351-357 | mhs4: 358-364 | mhs5: 365-371
-- mhs11 sem4: 372-378 | mhs12: 379-385 | mhs13: 386-392 | mhs14: 393-399
-- mhs6 sem2: 400-406 | mhs7: 407-413 | mhs8: 414-420 | mhs9: 421-427 | mhs10: 428-434 | mhs15: 435-441

INSERT IGNORE INTO nilai(krs_id,absen,tugas,uts,uas,nilai_akhir,nilai_huruf,bobot,published)
SELECT k.id,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 100 WHEN 2 THEN 100 WHEN 4 THEN 100 WHEN 6 THEN 100
    ELSE 85
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 88 WHEN 2 THEN 88 WHEN 4 THEN 88 WHEN 6 THEN 88
    ELSE 78
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 85 WHEN 2 THEN 85 WHEN 4 THEN 85 WHEN 6 THEN 85
    ELSE 75
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 90 WHEN 2 THEN 90 WHEN 4 THEN 90 WHEN 6 THEN 90
    ELSE 78
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 89.10 WHEN 2 THEN 89.10 WHEN 4 THEN 89.10 WHEN 6 THEN 89.10
    ELSE 77.80
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 'A' WHEN 2 THEN 'A' WHEN 4 THEN 'A' WHEN 6 THEN 'A'
    ELSE 'B'
  END,
  CASE ((k.id - 337) % 7)
    WHEN 0 THEN 4.00 WHEN 2 THEN 4.00 WHEN 4 THEN 4.00 WHEN 6 THEN 4.00
    ELSE 3.00
  END,
  1
FROM krs k
WHERE k.id >= 337 AND NOT EXISTS (SELECT 1 FROM nilai n WHERE n.krs_id = k.id);
