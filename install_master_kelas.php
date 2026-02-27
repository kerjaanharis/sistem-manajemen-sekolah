<?php
require_once 'config/database.php';

try {
    // 1. Update ENUM for status_siswa
    echo "Updating status_siswa ENUM in data_siswa...<br>";
    $pdo->exec("ALTER TABLE data_siswa MODIFY COLUMN status_siswa ENUM('Aktif', 'Lulus', 'Pindah', 'Keluar', 'Drop Out', 'Mutasi Keluar') DEFAULT 'Aktif'");

    // 2. Add tahun_lulus column if not exists
    echo "Adding tahun_lulus column to data_siswa...<br>";
    $stmt = $pdo->query("SHOW COLUMNS FROM data_siswa LIKE 'tahun_lulus'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE data_siswa ADD COLUMN tahun_lulus VARCHAR(10) NULL AFTER status_siswa");
    }

    // 3. Create master_kelas table
    echo "Creating master_kelas table...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS master_kelas (
        id_kelas INT AUTO_INCREMENT PRIMARY KEY,
        tahun_ajaran VARCHAR(20) NOT NULL,
        tingkat VARCHAR(10) NOT NULL,
        nama_kelas VARCHAR(50) NOT NULL,
        id_wali_kelas INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_wali_kelas) REFERENCES data_pegawai(id_pegawai) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Create anggota_kelas table
    echo "Creating anggota_kelas table...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS anggota_kelas (
        id_anggota INT AUTO_INCREMENT PRIMARY KEY,
        id_kelas INT NOT NULL,
        id_siswa INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_kelas) REFERENCES master_kelas(id_kelas) ON DELETE CASCADE,
        FOREIGN KEY (id_siswa) REFERENCES data_siswa(id_siswa) ON DELETE CASCADE,
        UNIQUE KEY unique_siswa_kelas (id_kelas, id_siswa)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "<h3>Semua tabel Master Kelas berhasil dibuat/diperbarui!</h3>";

} catch (PDOException $e) {
    echo "<h3>Error: " . $e->getMessage() . "</h3>";
}
