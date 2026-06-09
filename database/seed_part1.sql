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
('mhs8','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','hendra.l@mhs.sia.ac.id','MAHASISWA'),
('mhs9','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','indah@mhs.sia.ac.id','MAHASISWA'),
('mhs10','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','joko@mhs.sia.ac.id','MAHASISWA'),
('mhs11','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','kartika@mhs.sia.ac.id','MAHASISWA'),
('mhs12','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','luki@mhs.sia.ac.id','MAHASISWA'),
('mhs13','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','maya.a@mhs.sia.ac.id','MAHASISWA'),
('mhs14','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','nanda@mhs.sia.ac.id','MAHASISWA'),
('mhs15','$2y$10$/OX3UIWssBD1ew.xxqd0zOwFI8l9/H0rPLAeEBuokwWYs7h0v95RW','olivia@mhs.sia.ac.id','MAHASISWA');

INSERT INTO dosen(user_id,nip,nama,email,telepon,alamat)VALUES
(2,'198501012010011001','Bapak Ghalib, S.Kom., M.T.','ghalib@sia.ac.id','081234567890','Jl. Merdeka No.10, Pekanbaru'),
(3,'199002022015011002','Ibu Siti Aminah, M.Si','siti@sia.ac.id','082345678901','Jl. Sudirman No.25, Pekanbaru'),
(4,'199103032016011003','Dr. Ahmad Reza, M.Kom','reza@sia.ac.id','083456789012','Jl. Thamrin No.5, Pekanbaru'),
(5,'197805052008011004','Prof. Hendra Wijaya, Ph.D','hendra@sia.ac.id','084567890123','Jl. Diponegoro No.8, Pekanbaru'),
(6,'198807072012012005','Dr. Maya Susanti, M.T.','maya@sia.ac.id','085678901234','Jl. Imam Bonjol No.3, Pekanbaru'),
(7,'199210102018011006','Bapak Rizky Pratama, M.Kom','rizky@sia.ac.id','086789012345','Jl. Ahmad Yani No.15, Pekanbaru');

INSERT INTO mahasiswa(user_id,nim,nama,email,jurusan,angkatan,alamat,telepon)VALUES
(8, '210511001','Andi Saputra',   'andi@mhs.sia.ac.id',    'Teknik Informatika',2021,'Jl. Mawar No.12','081111111111'),
(9, '210511002','Budi Raharjo',   'budi@mhs.sia.ac.id',    'Teknik Informatika',2021,'Jl. Melati No.8','082222222222'),
(10,'210511003','Citra Kirana',   'citra@mhs.sia.ac.id',   'Sistem Informasi',  2021,'Jl. Kenanga No.3','083333333333'),
(11,'210511004','Dedi Kurniawan', 'dedi@mhs.sia.ac.id',    'Sistem Informasi',  2021,'Jl. Anggrek No.7','084444444444'),
(12,'210511005','Eka Putri',      'eka@mhs.sia.ac.id',     'Teknik Komputer',   2021,'Jl. Dahlia No.15','085555555555'),
(13,'220511001','Fajar Hidayat',  'fajar@mhs.sia.ac.id',   'Teknik Informatika',2022,'Jl. Flamboyan No.4','086666666666'),
(14,'220511002','Gita Permata',   'gita@mhs.sia.ac.id',    'Teknik Informatika',2022,'Jl. Cempaka No.9','087777777777'),
(15,'220511003','Hendra Laksana', 'hendra.l@mhs.sia.ac.id','Sistem Informasi',  2022,'Jl. Teratai No.6','088888888888'),
(16,'220511004','Indah Pratiwi',  'indah@mhs.sia.ac.id',   'Teknik Informatika',2022,'Jl. Seroja No.11','089999999999'),
(17,'220511005','Joko Santoso',   'joko@mhs.sia.ac.id',    'Teknik Komputer',   2022,'Jl. Wijaya No.2','081122334455'),
(18,'210511011','Kartika Dewi',   'kartika@mhs.sia.ac.id', 'Teknik Informatika',2021,'Jl. Nusantara No.5','082233445566'),
(19,'210511012','Luki Firmansyah','luki@mhs.sia.ac.id',    'Teknik Informatika',2021,'Jl. Pahlawan No.7','083344556677'),
(20,'210511013','Maya Anggraini', 'maya.a@mhs.sia.ac.id',  'Sistem Informasi',  2021,'Jl. Maju No.3','084455667788'),
(21,'210511014','Nanda Putra',    'nanda@mhs.sia.ac.id',   'Teknik Komputer',   2021,'Jl. Sejahtera No.9','085566778899'),
(22,'220511006','Olivia Sari',    'olivia@mhs.sia.ac.id',  'Teknik Informatika',2022,'Jl. Permata No.1','086677889900');

-- 42 KULIAH (7 per semester, 6 semester), dosen rotasi 1-6
INSERT INTO kuliah(kode_mk,nama_mk,sks,semester,dosen_id,hari,jam,ruang,kelas,kuota)VALUES
('TI101','Pemrograman Dasar',3,1,1,'Senin','08:00-10:30','Lab Komp 1','TI-1A',40),
('TI102','Matematika Diskrit',3,1,2,'Selasa','08:00-10:30','R.101','TI-1A',40),
('TI103','Pengantar Teknologi Informasi',2,1,3,'Rabu','08:00-09:40','R.102','TI-1A',40),
('TI104','Logika Informatika',3,1,4,'Kamis','08:00-10:30','R.103','TI-1B',40),
('TI105','Fisika Dasar',3,1,5,'Jumat','08:00-10:30','R.104','TI-1B',40),
('TI106','Bahasa Indonesia',2,1,6,'Senin','10:30-12:10','R.105','TI-1B',40),
('TI107','Kewarganegaraan',2,1,1,'Selasa','10:30-12:10','R.106','TI-1C',40),
('TI201','Struktur Data',3,2,2,'Senin','08:00-10:30','Lab Komp 1','TI-2A',40),
('TI202','Kalkulus Lanjut',3,2,3,'Selasa','08:00-10:30','R.201','TI-2A',40),
('TI203','Sistem Digital',3,2,4,'Rabu','08:00-10:30','R.202','TI-2A',40),
('TI204','Pemrograman Berorientasi Objek',3,2,5,'Kamis','10:30-13:00','Lab Komp 2','TI-2B',40),
('TI205','Statistika Dasar',3,2,6,'Jumat','10:30-13:00','R.203','TI-2B',40),
('TI206','Bahasa Inggris Teknik',2,2,1,'Senin','10:30-12:10','R.204','TI-2B',40),
('TI207','Elektronika Dasar',3,2,2,'Selasa','13:00-15:30','R.205','TI-2C',40),
('TI301','Basis Data',3,3,3,'Senin','13:00-15:30','Lab Komp 2','TI-3A',40),
('TI302','Algoritma & Pemrograman Lanjut',3,3,4,'Rabu','10:30-13:00','R.301','TI-3A',40),
('TI303','Jaringan Komputer',3,3,5,'Jumat','08:00-10:30','R.302','TI-3A',40),
('TI304','Sistem Operasi',3,3,6,'Kamis','13:00-15:30','R.303','TI-3B',40),
('TI305','Pemrograman Web',3,3,1,'Senin','08:00-10:30','Lab Komp 3','TI-3B',40),
('TI306','Komunikasi Data',3,3,2,'Rabu','13:00-15:30','R.304','TI-3B',40),
('TI307','Matematika Komputasi',3,3,3,'Selasa','13:00-15:30','R.305','TI-3C',40),
('TI401','Pemrograman Web Lanjut',3,4,4,'Senin','08:00-10:30','Lab Komp 1','TI-4A',40),
('TI402','Rekayasa Perangkat Lunak',3,4,5,'Rabu','13:00-15:30','R.401','TI-4A',40),
('TI403','Keamanan Jaringan',3,4,6,'Kamis','13:00-15:30','R.402','TI-4A',40),
('TI404','Manajemen Proyek',3,4,1,'Jumat','10:30-13:00','R.403','TI-4B',40),
('TI405','Mobile Programming',3,4,2,'Senin','13:00-15:30','Lab Komp 2','TI-4B',40),
('TI406','Pemrograman Fungsional',3,4,3,'Selasa','08:00-10:30','R.404','TI-4B',40),
('TI407','Grafika Komputer',3,4,4,'Rabu','08:00-10:30','R.405','TI-4C',40),
('TI501','Kecerdasan Buatan',3,5,5,'Selasa','13:00-15:30','R.501','TI-5A',40),
('TI502','Data Science',3,5,6,'Jumat','10:30-13:00','R.502','TI-5A',40),
('TI503','Machine Learning',3,5,1,'Senin','13:00-15:30','Lab Komp 3','TI-5A',40),
('TI504','Big Data Analytics',3,5,2,'Rabu','10:30-13:00','R.503','TI-5B',40),
('TI505','Computer Vision',3,5,3,'Kamis','08:00-10:30','R.504','TI-5B',40),
('TI506','Natural Language Processing',3,5,4,'Jumat','08:00-10:30','R.505','TI-5B',40),
('TI507','Kerja Praktek',3,5,5,'Senin','08:00-10:30','R.506','TI-5C',40),
('TI601','Keamanan Siber',3,6,6,'Senin','13:00-15:30','R.601','TI-6A',40),
('TI602','Manajemen Proyek TI',2,6,1,'Rabu','08:00-09:40','R.602','TI-6A',40),
('TI603','Sistem Terdistribusi',3,6,2,'Kamis','10:30-13:00','R.603','TI-6A',40),
('TI604','Cloud Computing',3,6,3,'Jumat','13:00-15:30','R.604','TI-6B',40),
('TI605','Internet of Things',3,6,4,'Selasa','08:00-10:30','Lab Komp 3','TI-6B',40),
('TI606','Ethical Hacking',3,6,5,'Rabu','13:00-15:30','R.605','TI-6B',40),
('TI607','Skripsi',6,6,6,'Senin','08:00-10:30','R.606','TI-6C',40);
