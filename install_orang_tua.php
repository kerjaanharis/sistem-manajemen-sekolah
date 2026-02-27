<?php
require 'config/database.php';

$columns = [
    // Ayah
    "status_ayah" => "ENUM('Hidup', 'Meninggal') DEFAULT 'Hidup'",
    "nik_ayah" => "VARCHAR(16) DEFAULT NULL",
    "nama_ayah" => "VARCHAR(100) DEFAULT NULL",
    "tahun_lahir_ayah" => "YEAR DEFAULT NULL",
    "pendidikan_ayah" => "VARCHAR(50) DEFAULT NULL",
    "pekerjaan_ayah" => "VARCHAR(50) DEFAULT NULL",
    "penghasilan_ayah" => "VARCHAR(50) DEFAULT NULL",
    "no_hp_ayah" => "VARCHAR(15) DEFAULT NULL",
    "alamat_ayah" => "TEXT DEFAULT NULL",

    // Ibu
    "status_ibu" => "ENUM('Hidup', 'Meninggal') DEFAULT 'Hidup'",
    "nik_ibu" => "VARCHAR(16) DEFAULT NULL",
    "nama_ibu" => "VARCHAR(100) DEFAULT NULL",
    "tahun_lahir_ibu" => "YEAR DEFAULT NULL",
    "pendidikan_ibu" => "VARCHAR(50) DEFAULT NULL",
    "pekerjaan_ibu" => "VARCHAR(50) DEFAULT NULL",
    "penghasilan_ibu" => "VARCHAR(50) DEFAULT NULL",
    "no_hp_ibu" => "VARCHAR(15) DEFAULT NULL",
    "alamat_ibu" => "TEXT DEFAULT NULL",

    // Wali
    "nik_wali" => "VARCHAR(16) DEFAULT NULL",
    "nama_wali" => "VARCHAR(100) DEFAULT NULL",
    "tahun_lahir_wali" => "YEAR DEFAULT NULL",
    "pendidikan_wali" => "VARCHAR(50) DEFAULT NULL",
    "pekerjaan_wali" => "VARCHAR(50) DEFAULT NULL",
    "penghasilan_wali" => "VARCHAR(50) DEFAULT NULL",
    "no_hp_wali" => "VARCHAR(15) DEFAULT NULL",
    "alamat_wali" => "TEXT DEFAULT NULL"
];

$success_count = 0;
$error_count = 0;

foreach ($columns as $column_name => $column_definition) {
    try {
        // Cek apakah kolom sudah ada
        $check_sql = "SHOW COLUMNS FROM data_siswa LIKE '{$column_name}'";
        $check_stmt = $pdo->query($check_sql);

        if ($check_stmt->rowCount() == 0) {
            $alter_sql = "ALTER TABLE data_siswa ADD COLUMN {$column_name} {$column_definition}";
            $pdo->exec($alter_sql);
            echo "Kolom '{$column_name}' berhasil ditambahkan.\n";
            $success_count++;
        } else {
            echo "Kolom '{$column_name}' sudah ada. Lewati.\n";
        }
    } catch (PDOException $e) {
        echo "Error menambah kolom '{$column_name}': " . $e->getMessage() . "\n";
        $error_count++;
    }
}

echo "\nSelesai! Berhasil menambah $success_count kolom. Error: $error_count.\n";
?>