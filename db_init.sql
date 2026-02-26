-- Pembuatan Database Inti PINTU KARTANEGARA
CREATE DATABASE IF NOT EXISTS db_pintu_kartanegara;
USE db_pintu_kartanegara;

-- 1. Tabel Master Roles
CREATE TABLE IF NOT EXISTS roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO roles (id_role, nama_role) VALUES 
(1, 'Administrator'), 
(2, 'Kepala Sekolah'), 
(3, 'Guru'), 
(4, 'Karyawan/TU'), 
(5, 'Siswa'),
(6, 'Wali Murid');

-- 2. Tabel Users (SSO Pusat)
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    id_role INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Default (Password: admin123)
-- bcrypt hash of 'admin123' is $2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm (example)
INSERT INTO users (id_user, username, password_hash, id_role) 
SELECT 1, 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 1 
WHERE NOT EXISTS (SELECT 1 FROM users WHERE id_user = 1);

-- 3. Tabel Data Siswa Inti
CREATE TABLE IF NOT EXISTS data_siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL, -- NULL jika belum punya akun SSO
    nis VARCHAR(20) NOT NULL UNIQUE,
    nisn VARCHAR(20) NULL UNIQUE,
    nama_lengkap VARCHAR(150) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    kelas VARCHAR(10) NOT NULL, -- Misal XII-RPL-1
    angkatan YEAR NULL,
    rfid_tag VARCHAR(50) NULL UNIQUE,
    koordinat_gis VARCHAR(100) NULL,
    status_siswa ENUM('Aktif', 'Lulus', 'Pindah', 'Keluar') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Data Pegawai (Guru dan Karyawan) Inti
CREATE TABLE IF NOT EXISTS data_pegawai (
    id_pegawai INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL, -- NULL jika belum punya akun SSO
    nip_nik VARCHAR(50) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(150) NOT NULL,
    tipe_pegawai ENUM('Guru', 'Karyawan') NOT NULL,
    jabatan VARCHAR(100) NULL, -- Contoh: Wali Kelas, Kepala Lab, dsb
    rfid_tag VARCHAR(50) NULL UNIQUE,
    status_pegawai ENUM('Aktif', 'Cuti', 'Mutasi', 'Pensiun') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Log Aktivitas Terpadu
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL,
    aksi VARCHAR(255) NOT NULL,
    modul_terkait VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes for performance
CREATE INDEX idx_siswa_kelas ON data_siswa(kelas);
CREATE INDEX idx_pegawai_tipe ON data_pegawai(tipe_pegawai);
CREATE INDEX idx_log_modul ON log_aktivitas(modul_terkait);
