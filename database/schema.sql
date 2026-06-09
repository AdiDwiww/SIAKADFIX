CREATE DATABASE IF NOT EXISTS uas_psi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uas_psi;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS nilai,krs,ips_history,kuliah,mahasiswa,dosen,users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,email VARCHAR(100) NOT NULL UNIQUE,role ENUM('ADMIN','DOSEN','MAHASISWA') NOT NULL DEFAULT 'MAHASISWA',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)ENGINE=InnoDB;
CREATE TABLE dosen(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL UNIQUE,nip VARCHAR(20) NOT NULL UNIQUE,nama VARCHAR(100) NOT NULL,email VARCHAR(100),telepon VARCHAR(20),alamat TEXT,foto VARCHAR(255),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE SET NULL)ENGINE=InnoDB;
CREATE TABLE mahasiswa(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL UNIQUE,nim VARCHAR(20) NOT NULL UNIQUE,nama VARCHAR(100) NOT NULL,email VARCHAR(100),telepon VARCHAR(20),alamat TEXT,jurusan VARCHAR(100),angkatan YEAR,foto VARCHAR(255),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id)REFERENCES users(id)ON DELETE SET NULL)ENGINE=InnoDB;
CREATE TABLE kuliah(id INT AUTO_INCREMENT PRIMARY KEY,kode_mk VARCHAR(20) NOT NULL UNIQUE,nama_mk VARCHAR(100) NOT NULL,sks TINYINT NOT NULL DEFAULT 3,semester TINYINT,dosen_id INT NULL,hari VARCHAR(20),jam VARCHAR(30),ruang VARCHAR(50),kelas VARCHAR(20),kuota INT DEFAULT 40,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(dosen_id)REFERENCES dosen(id)ON DELETE SET NULL)ENGINE=InnoDB;
CREATE TABLE krs(id INT AUTO_INCREMENT PRIMARY KEY,mahasiswa_id INT NOT NULL,kuliah_id INT NOT NULL,tahun_akademik VARCHAR(20) DEFAULT '2022/2023',semester_akademik ENUM('GANJIL','GENAP') DEFAULT 'GENAP',status ENUM('DRAFT','DIAJUKAN','DISETUJUI') DEFAULT 'DISETUJUI',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(mahasiswa_id)REFERENCES mahasiswa(id)ON DELETE CASCADE,FOREIGN KEY(kuliah_id)REFERENCES kuliah(id)ON DELETE CASCADE,UNIQUE KEY uq_krs(mahasiswa_id,kuliah_id,tahun_akademik,semester_akademik))ENGINE=InnoDB;
CREATE TABLE nilai(id INT AUTO_INCREMENT PRIMARY KEY,krs_id INT NOT NULL UNIQUE,absen DECIMAL(5,2),tugas DECIMAL(5,2),uts DECIMAL(5,2),uas DECIMAL(5,2),nilai_akhir DECIMAL(5,2),nilai_huruf CHAR(1),bobot DECIMAL(3,2),published TINYINT(1) DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(krs_id)REFERENCES krs(id)ON DELETE CASCADE)ENGINE=InnoDB;
CREATE TABLE ips_history(id INT AUTO_INCREMENT PRIMARY KEY,mahasiswa_id INT NOT NULL,semester INT NOT NULL,ips DECIMAL(4,2) NOT NULL,tahun_akademik VARCHAR(20),FOREIGN KEY(mahasiswa_id)REFERENCES mahasiswa(id)ON DELETE CASCADE,UNIQUE KEY uq_ips(mahasiswa_id,semester))ENGINE=InnoDB;

-- USERS: admin + 6 dosen + 15 mahasiswa
INSERT INTO users(username,password,email,role)VALUES
('admin','$2y$10$A0PRtD2JQvVRJC33UIW/Du/uVzxq1u8x91FgGdHmwYxfoafTBt8c.','admin@sia.ac.id','ADMIN'),
('dsn1','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','ghalib@sia.ac.id','DOSEN'),
('dsn2','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','siti@sia.ac.id','DOSEN'),
('dsn3','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','reza@sia.ac.id','DOSEN'),
('dsn4','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','hendra@sia.ac.id','DOSEN'),
('dsn5','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','maya@sia.ac.id','DOSEN'),
('dsn6','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','rizky@sia.ac.id','DOSEN'),
('mhs1','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','andi@mhs.sia.ac.id','MAHASISWA'),
('mhs2','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','budi@mhs.sia.ac.id','MAHASISWA'),
('mhs3','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','citra@mhs.sia.ac.id','MAHASISWA'),
('mhs4','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','dedi@mhs.sia.ac.id','MAHASISWA'),
('mhs5','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','eka@mhs.sia.ac.id','MAHASISWA'),
('mhs6','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','fajar@mhs.sia.ac.id','MAHASISWA'),
('mhs7','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','gita@mhs.sia.ac.id','MAHASISWA'),
('mhs8','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','hendra@mhs.sia.ac.id','MAHASISWA'),
('mhs9','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','indah@mhs.sia.ac.id','MAHASISWA'),
('mhs10','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','joko@mhs.sia.ac.id','MAHASISWA'),
('mhs11','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','kartika@mhs.sia.ac.id','MAHASISWA'),
('mhs12','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','luki@mhs.sia.ac.id','MAHASISWA'),
('mhs13','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','maya@mhs.sia.ac.id','MAHASISWA'),
('mhs14','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','nanda@mhs.sia.ac.id','MAHASISWA'),
('mhs15','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','olivia@mhs.sia.ac.id','MAHASISWA');

-- DOSEN (user_id 2-7)
INSERT INTO dosen(user_id,nip,nama,email,telepon,alamat)VALUES
(2,'198501012010011001','Bapak Ghalib, S.Kom., M.T.','ghalib@sia.ac.id','081234567890','Jl. Merdeka No.10, Pekanbaru'),
(3,'199002022015011002','Ibu Siti Aminah, M.Si','siti@sia.ac.id','082345678901','Jl. Sudirman No.25, Pekanbaru'),
(4,'199103032016011003','Dr. Ahmad Reza, M.Kom','reza@sia.ac.id','083456789012','Jl. Thamrin No.5, Pekanbaru'),
(5,'197805052008011004','Prof. Hendra Wijaya, Ph.D','hendra@sia.ac.id','084567890123','Jl. Diponegoro No.8, Pekanbaru'),
(6,'198807072012012005','Dr. Maya Susanti, M.T.','maya@sia.ac.id','085678901234','Jl. Imam Bonjol No.3, Pekanbaru'),
(7,'199210102018011006','Bapak Rizky Pratama, M.Kom','rizky@sia.ac.id','086789012345','Jl. Ahmad Yani No.15, Pekanbaru');

-- MAHASISWA (user_id 8-22)
INSERT INTO mahasiswa(user_id,nim,nama,email,jurusan,angkatan,alamat,telepon)VALUES
(8, '210511001','Andi Saputra',   'andi@mhs.sia.ac.id',  'Teknik Informatika',2021,'Jl. Mawar No.12','081111111111'),
(9, '210511002','Budi Raharjo',   'budi@mhs.sia.ac.id',  'Teknik Informatika',2021,'Jl. Melati No.8', '082222222222'),
(10,'210511003','Citra Kirana',   'citra@mhs.sia.ac.id', 'Sistem Informasi',  2021,'Jl. Kenanga No.3','083333333333'),
(11,'210511004','Dedi Kurniawan', 'dedi@mhs.sia.ac.id',  'Sistem Informasi',  2021,'Jl. Anggrek No.7','084444444444'),
(12,'210511005','Eka Putri',      'eka@mhs.sia.ac.id',   'Teknik Komputer',   2021,'Jl. Dahlia No.15','085555555555'),
(13,'220511001','Fajar Hidayat',  'fajar@mhs.sia.ac.id', 'Teknik Informatika',2022,'Jl. Flamboyan No.4','086666666666'),
(14,'220511002','Gita Permata',   'gita@mhs.sia.ac.id',  'Teknik Informatika',2022,'Jl. Cempaka No.9','087777777777'),
(15,'220511003','Hendra Laksana', 'hendra@mhs.sia.ac.id','Sistem Informasi',  2022,'Jl. Teratai No.6','088888888888'),
(16,'220511004','Indah Pratiwi',  'indah@mhs.sia.ac.id', 'Teknik Informatika',2022,'Jl. Seroja No.11','089999999999'),
(17,'220511005','Joko Santoso',   'joko@mhs.sia.ac.id',  'Teknik Komputer',   2022,'Jl. Wijaya No.2','081122334455'),
(18,'210511011','Kartika Dewi',   'kartika@mhs.sia.ac.id','Teknik Informatika',2021,'Jl. Nusantara No.5','082233445566'),
(19,'210511012','Luki Firmansyah','luki@mhs.sia.ac.id',  'Teknik Informatika',2021,'Jl. Pahlawan No.7','083344556677'),
(20,'210511013','Maya Anggraini', 'maya@mhs.sia.ac.id',  'Sistem Informasi',  2021,'Jl. Maju No.3','084455667788'),
(21,'210511014','Nanda Putra',    'nanda@mhs.sia.ac.id', 'Teknik Komputer',   2021,'Jl. Sejahtera No.9','085566778899'),
(22,'220511006','Olivia Sari',    'olivia@mhs.sia.ac.id','Teknik Informatika',2022,'Jl. Permata No.1','086677889900');

-- KULIAH (16 MK, dosen merata)
INSERT INTO kuliah(kode_mk,nama_mk,sks,semester,dosen_id,hari,jam,ruang,kelas,kuota)VALUES
('TI101','Pemrograman Dasar',3,1,1,'Senin','08:00-10:30','Lab Komputer 1','TI-1A',40),
('TI102','Matematika Diskrit',3,1,2,'Selasa','08:00-10:30','R.101','TI-1A',40),
('TI103','Pengantar Teknologi Informasi',2,1,3,'Rabu','08:00-09:40','R.102','TI-1B',40),
('TI201','Struktur Data',3,2,4,'Senin','10:30-13:00','Lab Komputer 1','TI-2A',40),
('TI202','Kalkulus Lanjut',3,2,5,'Selasa','10:30-13:00','R.201','TI-2A',40),
('TI203','Sistem Digital',3,2,6,'Kamis','08:00-10:30','R.202','TI-2B',40),
('TI301','Basis Data',3,3,1,'Senin','13:00-15:30','Lab Komputer 2','TI-3A',40),
('TI302','Algoritma & Pemrograman Lanjut',3,3,2,'Rabu','10:30-13:00','R.301','TI-3A',40),
('TI303','Jaringan Komputer',3,3,3,'Jumat','08:00-10:30','R.302','TI-3B',40),
('TI401','Pemrograman Web Lanjut',3,4,4,'Senin','08:00-10:30','Lab Komputer 1','TI-4A',40),
('TI402','Rekayasa Perangkat Lunak',3,4,5,'Rabu','13:00-15:30','R.401','TI-4A',40),
('TI403','Sistem Operasi',3,4,6,'Kamis','13:00-15:30','R.402','TI-4B',40),
('TI501','Kecerdasan Buatan',3,5,1,'Selasa','13:00-15:30','R.501','TI-5A',40),
('TI502','Data Science',3,5,2,'Jumat','10:30-13:00','R.502','TI-5A',40),
('TI601','Keamanan Siber',3,6,3,'Senin','13:00-15:30','R.601','TI-6A',40),
('TI602','Manajemen Proyek TI',2,6,4,'Rabu','08:00-09:40','R.602','TI-6A',40);
USE uas_psi;

-- ============================================================
-- KRS: mhs1-5,11-15 (angkatan2021) = sem1,2,3,4 | mhs6-10,15 (angkatan2022) = sem1,2
-- Sem1=2021/2022 GANJIL, Sem2=2021/2022 GENAP, Sem3=2022/2023 GANJIL, Sem4=2022/2023 GENAP(ACTIVE)
-- mhs6-10: Sem1=2022/2023 GANJIL, Sem2=2022/2023 GENAP(ACTIVE)
-- kuliah IDs: TI101=1,TI102=2,TI103=3,TI201=4,TI202=5,TI203=6,TI301=7,TI302=8,TI303=9,TI401=10,TI402=11,TI403=12
-- ============================================================
INSERT INTO krs(mahasiswa_id,kuliah_id,tahun_akademik,semester_akademik,status)VALUES
-- mhs1 (krs 1-8)
(1,1,'2021/2022','GANJIL','DISETUJUI'),(1,2,'2021/2022','GANJIL','DISETUJUI'),
(1,4,'2021/2022','GENAP','DISETUJUI'),(1,5,'2021/2022','GENAP','DISETUJUI'),
(1,7,'2022/2023','GANJIL','DISETUJUI'),(1,8,'2022/2023','GANJIL','DISETUJUI'),
(1,10,'2022/2023','GENAP','DISETUJUI'),(1,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs2 (krs 9-16)
(2,1,'2021/2022','GANJIL','DISETUJUI'),(2,2,'2021/2022','GANJIL','DISETUJUI'),
(2,4,'2021/2022','GENAP','DISETUJUI'),(2,5,'2021/2022','GENAP','DISETUJUI'),
(2,7,'2022/2023','GANJIL','DISETUJUI'),(2,8,'2022/2023','GANJIL','DISETUJUI'),
(2,10,'2022/2023','GENAP','DISETUJUI'),(2,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs3 (krs 17-24)
(3,1,'2021/2022','GANJIL','DISETUJUI'),(3,2,'2021/2022','GANJIL','DISETUJUI'),
(3,4,'2021/2022','GENAP','DISETUJUI'),(3,5,'2021/2022','GENAP','DISETUJUI'),
(3,7,'2022/2023','GANJIL','DISETUJUI'),(3,8,'2022/2023','GANJIL','DISETUJUI'),
(3,10,'2022/2023','GENAP','DISETUJUI'),(3,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs4 (krs 25-32)
(4,1,'2021/2022','GANJIL','DISETUJUI'),(4,2,'2021/2022','GANJIL','DISETUJUI'),
(4,4,'2021/2022','GENAP','DISETUJUI'),(4,5,'2021/2022','GENAP','DISETUJUI'),
(4,7,'2022/2023','GANJIL','DISETUJUI'),(4,8,'2022/2023','GANJIL','DISETUJUI'),
(4,10,'2022/2023','GENAP','DISETUJUI'),(4,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs5 (krs 33-40)
(5,1,'2021/2022','GANJIL','DISETUJUI'),(5,2,'2021/2022','GANJIL','DISETUJUI'),
(5,4,'2021/2022','GENAP','DISETUJUI'),(5,5,'2021/2022','GENAP','DISETUJUI'),
(5,7,'2022/2023','GANJIL','DISETUJUI'),(5,8,'2022/2023','GANJIL','DISETUJUI'),
(5,10,'2022/2023','GENAP','DISETUJUI'),(5,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs6 (krs 41-44) angkatan2022
(6,1,'2022/2023','GANJIL','DISETUJUI'),(6,3,'2022/2023','GANJIL','DISETUJUI'),
(6,4,'2022/2023','GENAP','DISETUJUI'),(6,6,'2022/2023','GENAP','DISETUJUI'),
-- mhs7 (krs 45-48)
(7,1,'2022/2023','GANJIL','DISETUJUI'),(7,3,'2022/2023','GANJIL','DISETUJUI'),
(7,4,'2022/2023','GENAP','DISETUJUI'),(7,6,'2022/2023','GENAP','DISETUJUI'),
-- mhs8 (krs 49-52)
(8,1,'2022/2023','GANJIL','DISETUJUI'),(8,3,'2022/2023','GANJIL','DISETUJUI'),
(8,4,'2022/2023','GENAP','DISETUJUI'),(8,6,'2022/2023','GENAP','DISETUJUI'),
-- mhs9 (krs 53-56)
(9,1,'2022/2023','GANJIL','DISETUJUI'),(9,3,'2022/2023','GANJIL','DISETUJUI'),
(9,4,'2022/2023','GENAP','DISETUJUI'),(9,6,'2022/2023','GENAP','DISETUJUI'),
-- mhs10 (krs 57-60)
(10,1,'2022/2023','GANJIL','DISETUJUI'),(10,3,'2022/2023','GANJIL','DISETUJUI'),
(10,4,'2022/2023','GENAP','DISETUJUI'),(10,6,'2022/2023','GENAP','DISETUJUI'),
-- mhs11 (krs 61-68) angkatan2021
(11,1,'2021/2022','GANJIL','DISETUJUI'),(11,2,'2021/2022','GANJIL','DISETUJUI'),
(11,4,'2021/2022','GENAP','DISETUJUI'),(11,5,'2021/2022','GENAP','DISETUJUI'),
(11,7,'2022/2023','GANJIL','DISETUJUI'),(11,8,'2022/2023','GANJIL','DISETUJUI'),
(11,10,'2022/2023','GENAP','DISETUJUI'),(11,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs12 (krs 69-76)
(12,1,'2021/2022','GANJIL','DISETUJUI'),(12,2,'2021/2022','GANJIL','DISETUJUI'),
(12,4,'2021/2022','GENAP','DISETUJUI'),(12,5,'2021/2022','GENAP','DISETUJUI'),
(12,7,'2022/2023','GANJIL','DISETUJUI'),(12,8,'2022/2023','GANJIL','DISETUJUI'),
(12,10,'2022/2023','GENAP','DISETUJUI'),(12,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs13 (krs 77-84)
(13,1,'2021/2022','GANJIL','DISETUJUI'),(13,2,'2021/2022','GANJIL','DISETUJUI'),
(13,4,'2021/2022','GENAP','DISETUJUI'),(13,5,'2021/2022','GENAP','DISETUJUI'),
(13,7,'2022/2023','GANJIL','DISETUJUI'),(13,8,'2022/2023','GANJIL','DISETUJUI'),
(13,10,'2022/2023','GENAP','DISETUJUI'),(13,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs14 (krs 85-92)
(14,1,'2021/2022','GANJIL','DISETUJUI'),(14,2,'2021/2022','GANJIL','DISETUJUI'),
(14,4,'2021/2022','GENAP','DISETUJUI'),(14,5,'2021/2022','GENAP','DISETUJUI'),
(14,7,'2022/2023','GANJIL','DISETUJUI'),(14,8,'2022/2023','GANJIL','DISETUJUI'),
(14,10,'2022/2023','GENAP','DISETUJUI'),(14,11,'2022/2023','GENAP','DISETUJUI'),
-- mhs15 (krs 93-96) angkatan2022
(15,1,'2022/2023','GANJIL','DISETUJUI'),(15,3,'2022/2023','GANJIL','DISETUJUI'),
(15,4,'2022/2023','GENAP','DISETUJUI'),(15,6,'2022/2023','GENAP','DISETUJUI');

-- ============================================================
-- NILAI: (krs_id,absen,tugas,uts,uas,akhir,huruf,bobot,published)
-- A=89.1/4.0  B=77.8/3.0  C=64.0/2.0
-- mhs1=A,B pattern | mhs2=B,B | mhs3=A,A | mhs4=B,C | mhs5=A,B
-- mhs6=B,A | mhs7=A,A | mhs8=B,B | mhs9=A,B | mhs10=B,C
-- mhs11=A,A | mhs12=B,B | mhs13=A,B | mhs14=B,C | mhs15=A,B
-- ============================================================
INSERT INTO nilai(krs_id,absen,tugas,uts,uas,nilai_akhir,nilai_huruf,bobot,published)VALUES
-- mhs1: A,B,A,B,A,B,A,B (krs 1-8)
(1, 100,88,85,90,89.10,'A',4.00,1),(2, 85,78,75,78,77.80,'B',3.00,1),
(3, 100,88,85,90,89.10,'A',4.00,1),(4, 85,78,75,78,77.80,'B',3.00,1),
(5, 100,88,85,90,89.10,'A',4.00,1),(6, 85,78,75,78,77.80,'B',3.00,1),
(7, 100,88,85,90,89.10,'A',4.00,1),(8, 85,78,75,78,77.80,'B',3.00,1),
-- mhs2: B,B (krs 9-16)
(9, 85,78,75,78,77.80,'B',3.00,1),(10,85,78,75,78,77.80,'B',3.00,1),
(11,85,78,75,78,77.80,'B',3.00,1),(12,85,78,75,78,77.80,'B',3.00,1),
(13,85,78,75,78,77.80,'B',3.00,1),(14,85,78,75,78,77.80,'B',3.00,1),
(15,85,78,75,78,77.80,'B',3.00,1),(16,85,78,75,78,77.80,'B',3.00,1),
-- mhs3: A,A (krs 17-24)
(17,100,92,90,93,92.10,'A',4.00,1),(18,100,92,90,93,92.10,'A',4.00,1),
(19,100,92,90,93,92.10,'A',4.00,1),(20,100,92,90,93,92.10,'A',4.00,1),
(21,100,92,90,93,92.10,'A',4.00,1),(22,100,92,90,93,92.10,'A',4.00,1),
(23,100,92,90,93,92.10,'A',4.00,1),(24,100,92,90,93,92.10,'A',4.00,1),
-- mhs4: B,C (krs 25-32)
(25,85,78,75,78,77.80,'B',3.00,1),(26,70,65,60,65,64.00,'C',2.00,1),
(27,85,78,75,78,77.80,'B',3.00,1),(28,70,65,60,65,64.00,'C',2.00,1),
(29,85,78,75,78,77.80,'B',3.00,1),(30,70,65,60,65,64.00,'C',2.00,1),
(31,85,78,75,78,77.80,'B',3.00,1),(32,70,65,60,65,64.00,'C',2.00,1),
-- mhs5: A,B (krs 33-40)
(33,100,88,85,90,89.10,'A',4.00,1),(34,85,78,75,78,77.80,'B',3.00,1),
(35,100,88,85,90,89.10,'A',4.00,1),(36,85,78,75,78,77.80,'B',3.00,1),
(37,100,88,85,90,89.10,'A',4.00,1),(38,85,78,75,78,77.80,'B',3.00,1),
(39,100,88,85,90,89.10,'A',4.00,1),(40,85,78,75,78,77.80,'B',3.00,1),
-- mhs6: B,A (krs 41-44)
(41,85,78,75,78,77.80,'B',3.00,1),(42,100,88,85,90,89.10,'A',4.00,1),
(43,85,78,75,78,77.80,'B',3.00,1),(44,100,88,85,90,89.10,'A',4.00,1),
-- mhs7: A,A (krs 45-48)
(45,100,92,90,93,92.10,'A',4.00,1),(46,100,92,90,93,92.10,'A',4.00,1),
(47,100,92,90,93,92.10,'A',4.00,1),(48,100,92,90,93,92.10,'A',4.00,1),
-- mhs8: B,B (krs 49-52)
(49,85,78,75,78,77.80,'B',3.00,1),(50,85,78,75,78,77.80,'B',3.00,1),
(51,85,78,75,78,77.80,'B',3.00,1),(52,85,78,75,78,77.80,'B',3.00,1),
-- mhs9: A,B (krs 53-56)
(53,100,88,85,90,89.10,'A',4.00,1),(54,85,78,75,78,77.80,'B',3.00,1),
(55,100,88,85,90,89.10,'A',4.00,1),(56,85,78,75,78,77.80,'B',3.00,1),
-- mhs10: B,C (krs 57-60)
(57,85,78,75,78,77.80,'B',3.00,1),(58,70,65,60,65,64.00,'C',2.00,1),
(59,85,78,75,78,77.80,'B',3.00,1),(60,70,65,60,65,64.00,'C',2.00,1),
-- mhs11: A,A (krs 61-68)
(61,100,92,90,93,92.10,'A',4.00,1),(62,100,92,90,93,92.10,'A',4.00,1),
(63,100,92,90,93,92.10,'A',4.00,1),(64,100,92,90,93,92.10,'A',4.00,1),
(65,100,92,90,93,92.10,'A',4.00,1),(66,100,92,90,93,92.10,'A',4.00,1),
(67,100,92,90,93,92.10,'A',4.00,1),(68,100,92,90,93,92.10,'A',4.00,1),
-- mhs12: B,B (krs 69-76)
(69,85,78,75,78,77.80,'B',3.00,1),(70,85,78,75,78,77.80,'B',3.00,1),
(71,85,78,75,78,77.80,'B',3.00,1),(72,85,78,75,78,77.80,'B',3.00,1),
(73,85,78,75,78,77.80,'B',3.00,1),(74,85,78,75,78,77.80,'B',3.00,1),
(75,85,78,75,78,77.80,'B',3.00,1),(76,85,78,75,78,77.80,'B',3.00,1),
-- mhs13: A,B (krs 77-84)
(77,100,88,85,90,89.10,'A',4.00,1),(78,85,78,75,78,77.80,'B',3.00,1),
(79,100,88,85,90,89.10,'A',4.00,1),(80,85,78,75,78,77.80,'B',3.00,1),
(81,100,88,85,90,89.10,'A',4.00,1),(82,85,78,75,78,77.80,'B',3.00,1),
(83,100,88,85,90,89.10,'A',4.00,1),(84,85,78,75,78,77.80,'B',3.00,1),
-- mhs14: B,C (krs 85-92)
(85,85,78,75,78,77.80,'B',3.00,1),(86,70,65,60,65,64.00,'C',2.00,1),
(87,85,78,75,78,77.80,'B',3.00,1),(88,70,65,60,65,64.00,'C',2.00,1),
(89,85,78,75,78,77.80,'B',3.00,1),(90,70,65,60,65,64.00,'C',2.00,1),
(91,85,78,75,78,77.80,'B',3.00,1),(92,70,65,60,65,64.00,'C',2.00,1),
-- mhs15: A,B (krs 93-96)
(93,100,88,85,90,89.10,'A',4.00,1),(94,85,78,75,78,77.80,'B',3.00,1),
(95,100,88,85,90,89.10,'A',4.00,1),(96,85,78,75,78,77.80,'B',3.00,1);

-- ============================================================
-- IPS_HISTORY: past sems only (not current sem4 for2021/sem2 for2022)
-- mhs1-5,11-15: sem1=3.50,sem2=3.50,sem3=3.50 | mhs2,8,12: 3.00 | mhs3,7,11: 4.00 | mhs4,10,14: 2.50
-- mhs6-10,15: sem1 only
-- ============================================================
INSERT INTO ips_history(mahasiswa_id,semester,ips,tahun_akademik)VALUES
-- mhs1 (A/B=3.5)
(1,1,3.50,'2021/2022'),(1,2,3.50,'2021/2022'),(1,3,3.50,'2022/2023'),
-- mhs2 (B/B=3.0)
(2,1,3.00,'2021/2022'),(2,2,3.00,'2021/2022'),(2,3,3.00,'2022/2023'),
-- mhs3 (A/A=4.0)
(3,1,4.00,'2021/2022'),(3,2,4.00,'2021/2022'),(3,3,4.00,'2022/2023'),
-- mhs4 (B/C=2.5)
(4,1,2.50,'2021/2022'),(4,2,2.50,'2021/2022'),(4,3,2.50,'2022/2023'),
-- mhs5 (A/B=3.5)
(5,1,3.50,'2021/2022'),(5,2,3.50,'2021/2022'),(5,3,3.50,'2022/2023'),
-- mhs6 (B/A, sem1 only history)
(6,1,3.50,'2022/2023'),
-- mhs7 (A/A)
(7,1,4.00,'2022/2023'),
-- mhs8 (B/B)
(8,1,3.00,'2022/2023'),
-- mhs9 (A/B)
(9,1,3.50,'2022/2023'),
-- mhs10 (B/C)
(10,1,2.50,'2022/2023'),
-- mhs11 (A/A=4.0)
(11,1,4.00,'2021/2022'),(11,2,4.00,'2021/2022'),(11,3,4.00,'2022/2023'),
-- mhs12 (B/B=3.0)
(12,1,3.00,'2021/2022'),(12,2,3.00,'2021/2022'),(12,3,3.00,'2022/2023'),
-- mhs13 (A/B=3.5)
(13,1,3.50,'2021/2022'),(13,2,3.50,'2021/2022'),(13,3,3.50,'2022/2023'),
-- mhs14 (B/C=2.5)
(14,1,2.50,'2021/2022'),(14,2,2.50,'2021/2022'),(14,3,2.50,'2022/2023'),
-- mhs15 (A/B, sem1 history)
(15,1,3.50,'2022/2023');
