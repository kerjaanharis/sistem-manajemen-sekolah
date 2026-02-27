<?php
require 'config/database.php';

try {
    $check_sql = "SHOW COLUMNS FROM data_siswa LIKE 'hubungan_wali'";
    $check_stmt = $pdo->query($check_sql);

    if ($check_stmt->rowCount() == 0) {
        $alter_sql = "ALTER TABLE data_siswa ADD COLUMN hubungan_wali VARCHAR(50) DEFAULT NULL";
        $pdo->exec($alter_sql);
        echo "Kolom 'hubungan_wali' berhasil ditambahkan.\n";
    } else {
        echo "Kolom 'hubungan_wali' sudah ada.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>