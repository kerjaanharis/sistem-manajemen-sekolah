<?php
require 'config/database.php';
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS master_tahun_ajaran (
            id_ta INT AUTO_INCREMENT PRIMARY KEY, 
            tahun_ajaran VARCHAR(20) NOT NULL, 
            semester ENUM('Ganjil', 'Genap') NOT NULL, 
            is_active TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY ta_semester (tahun_ajaran, semester)
        );
        
        -- Insert dummy data if table is empty
        INSERT IGNORE INTO master_tahun_ajaran (tahun_ajaran, semester, is_active) 
        VALUES 
            ('2024/2025', 'Ganjil', 0), 
            ('2024/2025', 'Genap', 0),
            ('2025/2026', 'Ganjil', 1);
            
        -- Nonaktifkan is_active untuk baris yg ada di pengaturan_sistem
        -- Hapus baris pengaturan_sistem lama karena sudah tidak dipakai? 
        DELETE FROM pengaturan_sistem WHERE kunci IN ('tahun_ajaran', 'semester_aktif');
    ");
    echo "Tabel master_tahun_ajaran berhasil dibuat.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
