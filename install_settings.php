<?php
require 'config/database.php';
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pengaturan_sistem (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            kunci VARCHAR(50) NOT NULL UNIQUE, 
            nilai VARCHAR(255) NOT NULL, 
            keterangan VARCHAR(255), 
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        INSERT IGNORE INTO pengaturan_sistem (kunci, nilai, keterangan) 
        VALUES 
            ('tahun_ajaran', '2025/2026', 'Tahun Ajaran Aktif'), 
            ('semester_aktif', 'Ganjil', 'Semester Berjalan (Ganjil/Genap)');
    ");
    echo "Tabel berhasil dibuat.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
