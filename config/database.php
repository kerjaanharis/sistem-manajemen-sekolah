<?php
// Konfigurasi Database menggunakan PDO
$host = '127.0.0.1';
$dbname = 'db_pintu_kartanegara';
$username = 'root';
$password = ''; // Sesuaikan dengan password database local Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // Pada production, jangan tampilkan pesan error PDO secara langsung
    die("Koneksi Database Gagal: " . $e->getMessage());
}
