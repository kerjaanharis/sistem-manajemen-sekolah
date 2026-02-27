<?php
/**
 * Script to install 'siswa_bantuan' table
 */
require_once 'config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `siswa_bantuan` (
        `id_bantuan` int(11) NOT NULL AUTO_INCREMENT,
        `id_siswa` int(11) NOT NULL,
        `jenis_bantuan` enum('Program Indonesia Pintar (PIP)','Gerakan Nasional Orang Tua Asuh (GNOTA)','Program Keluarga Harapan (PKH)','Beasiswa Dari Pemerintah','Beasiswa Dari Sekolah','Program Bantuan Lain') NOT NULL,
        `tahun_diterima` varchar(20) DEFAULT NULL,
        `keterangan` text DEFAULT NULL,
        `created_at` timestamp DEFAULT current_timestamp(),
        PRIMARY KEY (`id_bantuan`),
        KEY `fk_bantuan_siswa` (`id_siswa`),
        CONSTRAINT `fk_bantuan_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Tabel 'siswa_bantuan' berhasil dibuat atau sudah ada.<br>";

    // Update db_init.sql file
    $db_init_path = __DIR__ . '/db_init.sql';
    if (file_exists($db_init_path)) {
        $db_init_content = file_get_contents($db_init_path);
        if (strpos($db_init_content, 'siswa_bantuan') === false) {
            $append_sql = "\n\n-- Tabel Siswa Penerima Bantuan\n" . $sql . "\n";
            file_put_contents($db_init_path, $append_sql, FILE_APPEND);
            echo "Skema berhasil ditambahkan ke db_init.sql.<br>";
        }
    }

    echo "<br><a href='index.php'>Kembali ke Dashboard</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>