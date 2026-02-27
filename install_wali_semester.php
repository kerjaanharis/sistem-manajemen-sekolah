<?php
require_once 'config/database.php';

echo "<h2>Migrasi Database: Wali Kelas per Semester</h2>";

try {
    $pdo->beginTransaction();

    // 1. Cek apakan kolom id_wali_kelas lama masih ada
    $stmt = $pdo->query("SHOW COLUMNS FROM master_kelas LIKE 'id_wali_kelas'");
    $has_old_wali = $stmt->rowCount() > 0;

    // 2. Tambah kolom baru id_wali_kelas_ganjil dan id_wali_kelas_genap jika belum ada
    $stmt_ganjil = $pdo->query("SHOW COLUMNS FROM master_kelas LIKE 'id_wali_kelas_ganjil'");
    if ($stmt_ganjil->rowCount() == 0) {
        $pdo->exec("ALTER TABLE master_kelas ADD COLUMN id_wali_kelas_ganjil INT NULL AFTER nama_kelas");
        $pdo->exec("ALTER TABLE master_kelas ADD CONSTRAINT fk_wali_ganjil FOREIGN KEY (id_wali_kelas_ganjil) REFERENCES data_pegawai(id_pegawai) ON DELETE SET NULL");
        echo "Kolom 'id_wali_kelas_ganjil' berhasil ditambahkan.<br>";
    } else {
        echo "Kolom 'id_wali_kelas_ganjil' sudah ada.<br>";
    }

    $stmt_genap = $pdo->query("SHOW COLUMNS FROM master_kelas LIKE 'id_wali_kelas_genap'");
    if ($stmt_genap->rowCount() == 0) {
        $pdo->exec("ALTER TABLE master_kelas ADD COLUMN id_wali_kelas_genap INT NULL AFTER id_wali_kelas_ganjil");
        $pdo->exec("ALTER TABLE master_kelas ADD CONSTRAINT fk_wali_genap FOREIGN KEY (id_wali_kelas_genap) REFERENCES data_pegawai(id_pegawai) ON DELETE SET NULL");
        echo "Kolom 'id_wali_kelas_genap' berhasil ditambahkan.<br>";
    } else {
        echo "Kolom 'id_wali_kelas_genap' sudah ada.<br>";
    }

    // 3. Pindahkan data wali kelas lama ke dua kolom baru (copy ke ganjil dan genap)
    if ($has_old_wali) {
        echo "Memindahkan data 'id_wali_kelas' lama ke kolom ganjil & genap...<br>";
        $pdo->exec("UPDATE master_kelas SET id_wali_kelas_ganjil = id_wali_kelas, id_wali_kelas_genap = id_wali_kelas WHERE id_wali_kelas IS NOT NULL");

        // 4. Hapus constraint lama dan drop kolom lama
        // Find existing constraint name (usually master_kelas_ibfk_X)
        $stmt_fk = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'master_kelas' AND COLUMN_NAME = 'id_wali_kelas'");
        if ($fk_row = $stmt_fk->fetch()) {
            $fk_name = $fk_row['CONSTRAINT_NAME'];
            $pdo->exec("ALTER TABLE master_kelas DROP FOREIGN KEY $fk_name");
            echo "Constraint '$fk_name' berhasil dihapus.<br>";
        }

        // Drop the old column
        $pdo->exec("ALTER TABLE master_kelas DROP COLUMN id_wali_kelas");
        echo "Kolom lama 'id_wali_kelas' berhasil dihapus.<br>";
    } else {
        echo "Tidak ditemukan kolom 'id_wali_kelas' lama. Tidak ada migrasi data yang diperlukan.<br>";
    }

    $pdo->commit();
    echo "<h3 style='color:green;'>Migrasi Wali Kelas Selesai!</h3>";
    echo "<a href='index.php'>Kembali ke Dashboard</a>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h3 style='color:red;'>Terjadi Kesalahan: " . $e->getMessage() . "</h3>";
}
?>