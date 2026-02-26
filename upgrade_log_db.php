<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("ALTER TABLE log_aktivitas 
        ADD COLUMN peran VARCHAR(50) DEFAULT NULL AFTER id_user,
        ADD COLUMN nama_user VARCHAR(100) DEFAULT NULL AFTER peran,
        ADD COLUMN aktifitas VARCHAR(100) DEFAULT NULL AFTER nama_user,
        ADD COLUMN keterangan_tambahan TEXT DEFAULT NULL AFTER aktifitas;
    ");
    echo "Tabel log_aktivitas berhasil di-upgrade.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Kolom sudah ada.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
