<?php
require_once 'config/database.php';

try {
    $pdo->exec("ALTER TABLE log_aktivitas MODIFY aksi VARCHAR(255) NULL, MODIFY modul_terkait VARCHAR(100) NULL");

    // Convert old logs
    $pdo->exec("UPDATE log_aktivitas SET aktifitas = aksi, keterangan_tambahan = CONCAT('Modul lama: ', modul_terkait) WHERE aktifitas IS NULL");

    echo "Tabel berhasil diperbaiki.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
