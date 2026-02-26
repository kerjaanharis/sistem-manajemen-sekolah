<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u LEFT JOIN roles r ON u.id_role = r.id_role WHERE u.username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        print_r($admin);
    } else {
        echo "Admin NOT found!\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
