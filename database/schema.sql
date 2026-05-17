-- ============================================================
--  PSI UAS – Sistem Informasi Akademik
--  Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS uas_psi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uas_psi;

-- -------------------------------------------------------
-- 1. users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    role       ENUM('ADMIN','DOSEN','MAHASISWA') NOT NULL DEFAULT 'MAHASISWA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- 2. dosen
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS dosen (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NULL UNIQUE,
    nip        VARCHAR(20)  NOT NULL UNIQUE,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(100),
    telepon    VARCHAR(20),
    alamat     TEXT,
    foto       VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- 3. mahasiswa
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS mahasiswa (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NULL UNIQUE,
    nim        VARCHAR(20)  NOT NULL UNIQUE,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(100),
    telepon    VARCHAR(20),
    alamat     TEXT,
    jurusan    VARCHAR(100),
    angkatan   YEAR,
    foto       VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- 4. kuliah (mata kuliah)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS kuliah (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk    VARCHAR(20)  NOT NULL UNIQUE,
    nama_mk    VARCHAR(100) NOT NULL,
    sks        TINYINT      NOT NULL DEFAULT 2,
    semester   TINYINT,
    dosen_id   INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Default ADMIN user  (password: admin123)
-- -------------------------------------------------------
INSERT IGNORE INTO users (username, password, email, role) VALUES
('admin', '$2y$10$TKh8H1.PfunckVrE/A7tDuS0WxFADNIbFCOh8/FpC7w.3Cl2qLbi6', 'admin@sia.ac.id', 'ADMIN');

-- Sample dosen
INSERT IGNORE INTO dosen (nip, nama, email, telepon) VALUES
('198501012010011001', 'Dr. Andi Susanto, M.Kom', 'andi@sia.ac.id', '081234567890'),
('199002022015011002', 'Siti Rahayu, S.T., M.T.', 'siti@sia.ac.id', '082345678901');

-- Sample mahasiswa
INSERT IGNORE INTO mahasiswa (nim, nama, email, jurusan, angkatan) VALUES
('2023110001', 'Budi Santoso',   'budi@mhs.ac.id',   'Teknik Informatika', 2023),
('2023110002', 'Dewi Lestari',   'dewi@mhs.ac.id',   'Teknik Informatika', 2023),
('2023110003', 'Fajar Nugroho',  'fajar@mhs.ac.id',  'Sistem Informasi',   2023),
('2022110004', 'Hani Putri',     'hani@mhs.ac.id',   'Sistem Informasi',   2022),
('2022110005', 'Ivan Kurniawan', 'ivan@mhs.ac.id',   'Teknik Informatika', 2022);

-- Sample kuliah
INSERT IGNORE INTO kuliah (kode_mk, nama_mk, sks, semester, dosen_id) VALUES
('TI-101', 'Pemrograman Web',          3, 3, 1),
('TI-102', 'Basis Data',               3, 3, 2),
('TI-201', 'Rekayasa Perangkat Lunak', 3, 4, 1),
('SI-101', 'Sistem Informasi',         2, 2, 2);
