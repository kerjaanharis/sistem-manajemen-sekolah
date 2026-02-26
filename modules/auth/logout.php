<?php
session_start();
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (isset($_SESSION['user_id'])) {

    try {
        // Rekam aktivitas Logout
        catat_log($pdo, 'Logout', 'Keluar dari sistem PINTU KARTANEGARA');
    } catch (\PDOException $e) {
        // Abaikan error log aktivitas jika database belum siap
    }
}

// Hapus semua session
session_unset();
session_destroy();

// Hapus cookies session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect ke halaman login
header("Location: " . base_url('modules/auth/login.php'));
exit;
