<?php
require_once 'config/database.php';

try {
    // Migrate old user names and roles
    $pdo->exec("
        UPDATE log_aktivitas l
        JOIN users u ON l.id_user = u.id_user
        JOIN roles r ON u.id_role = r.id_role
        SET l.nama_user = u.username, l.peran = r.nama_role
        WHERE l.nama_user IS NULL
    ");
    echo "Update data lama berhasil.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
