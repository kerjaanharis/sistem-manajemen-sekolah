<?php
// Script Setup Database PINTU KARTANEGARA (otomatis hapus diri sendiri)
$host = '127.0.0.1';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/db_init.sql');
    $pdo->exec($sql);

    echo "SUCCESS: Database and tables created successfully.";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
