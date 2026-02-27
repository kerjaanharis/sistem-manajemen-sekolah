<?php
require_once 'config/database.php';

echo "<h2>Instalasi Database: Master Tugas Tambahan</h2>";

try {
    $pdo->beginTransaction();

    // 1. Create tabel master_tugas
    $pdo->exec("CREATE TABLE IF NOT EXISTS master_tugas (
        id_tugas INT AUTO_INCREMENT PRIMARY KEY,
        nama_tugas VARCHAR(100) NOT NULL UNIQUE,
        kategori ENUM('Wakil Kepala Sekolah', 'Kepala Program/Unit', 'Koordinator/Pembina', 'Lainnya') DEFAULT 'Lainnya'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabel 'master_tugas' berhasil dibuat/sudah ada.<br>";

    // 2. Insert Default Roles
    $default_roles = [
        ['Waka Kurikulum', 'Wakil Kepala Sekolah'],
        ['Waka Kesiswaan', 'Wakil Kepala Sekolah'],
        ['Waka Sarpra', 'Wakil Kepala Sekolah'],
        ['Waka Humas', 'Wakil Kepala Sekolah'],
        ['Waka SDM', 'Wakil Kepala Sekolah'],
        ['Staf Waka Kurikulum', 'Wakil Kepala Sekolah'],
        ['Staf Waka Kesiswaan', 'Wakil Kepala Sekolah'],
        ['Staf Waka Sarpra', 'Wakil Kepala Sekolah'],
        ['Staf Waka Humas', 'Wakil Kepala Sekolah'],
        ['Staf Waka SDM', 'Wakil Kepala Sekolah'],
        ['Kepala TU', 'Kepala Program/Unit'],
        ['Kepala Perpustakaan', 'Kepala Program/Unit'],
        ['Kepala Program Keahlian', 'Kepala Program/Unit'],
        ['Kepala Bengkel', 'Kepala Program/Unit'],
        ['Kepala Laboratorium', 'Kepala Program/Unit'],
        ['Kepala Unit Produksi', 'Kepala Program/Unit'],
        ['Ketua Bursa Kerja Khusus (BKK)', 'Kepala Program/Unit'],
        ['Koordinator IT Sekolah', 'Koordinator/Pembina'],
        ['Koordinator Laboratorium', 'Koordinator/Pembina'],
        ['Koordinator Penegak Disiplin Sekolah (PDS)', 'Koordinator/Pembina'],
        ['Koordinator Petugas Tertib Administrasi Siswa', 'Koordinator/Pembina'],
        ['Koordinator Ekstrakurikuler', 'Koordinator/Pembina'],
        ['Koordinator Pembelajaran Berbasis Proyek', 'Koordinator/Pembina'],
        ['Tim Pengembang Kurikulum', 'Koordinator/Pembina'],
        ['Ka. Pokja KI/Skal', 'Koordinator/Pembina'],
        ['Pembina Ekstrakurikuler', 'Koordinator/Pembina'],
        ['Pembina OSIS', 'Koordinator/Pembina'],
        ['Pembina Pramuka', 'Koordinator/Pembina'],
        ['Pembina Pramuka Putra', 'Koordinator/Pembina'],
        ['Pembina Pramuka Putri', 'Koordinator/Pembina'],
        ['Anggota BKK', 'Lainnya'],
        ['Anggota IT Sekolah', 'Lainnya'],
        ['Anggota PDS', 'Lainnya'],
        ['Anggota PTAS', 'Lainnya'],
        ['Bendahara BOSP', 'Lainnya'],
        ['Bendahara BPOPP', 'Lainnya'],
        ['Bendahara Komite', 'Lainnya'],
        ['Operator Sekolah', 'Lainnya'],
        ['Operator BOSP', 'Lainnya'],
        ['Operator E-Raport', 'Lainnya'],
        ['Operator Dapodik', 'Lainnya'],
        ['Walikelas', 'Lainnya'],
        ['Guru Piket', 'Lainnya']
    ];

    $stmt_cek_tugas = $pdo->prepare("SELECT COUNT(*) FROM master_tugas WHERE nama_tugas = ?");
    $stmt_insert_tugas = $pdo->prepare("INSERT INTO master_tugas (nama_tugas, kategori) VALUES (?, ?)");

    $inserted = 0;
    foreach ($default_roles as $role) {
        $stmt_cek_tugas->execute([$role[0]]);
        if ($stmt_cek_tugas->fetchColumn() == 0) {
            $stmt_insert_tugas->execute([$role[0], $role[1]]);
            $inserted++;
        }
    }
    echo "$inserted tugas tambahan default berhasil dimasukkan.<br>";

    // 3. Create Tabel penugasan_pegawai
    $pdo->exec("CREATE TABLE IF NOT EXISTS penugasan_pegawai (
        id_penugasan INT AUTO_INCREMENT PRIMARY KEY,
        id_pegawai INT NOT NULL,
        id_tugas INT NOT NULL,
        tahun_ajaran VARCHAR(20) NOT NULL,
        semester ENUM('Ganjil', 'Genap') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pegawai) REFERENCES data_pegawai(id_pegawai) ON DELETE CASCADE,
        FOREIGN KEY (id_tugas) REFERENCES master_tugas(id_tugas) ON DELETE CASCADE,
        UNIQUE KEY unique_penugasan (id_pegawai, id_tugas, tahun_ajaran, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabel 'penugasan_pegawai' berhasil dibuat/sudah ada.<br>";

    $pdo->commit();
    echo "<h3 style='color:green;'>Instalasi Master Tugas Tambahan Selesai!</h3>";
    echo "<a href='index.php'>Kembali ke Dashboard</a>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h3 style='color:red;'>Terjadi Kesalahan: " . $e->getMessage() . "</h3>";
}
?>