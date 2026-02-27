<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Utama Sistem
define('BASE_URL', 'http://localhost/sistem-manajemen-sekolah');
define('APP_NAME', 'PINTU KARTANEGARA');
define('APP_DESC', 'Pusat Informasi Terpadu Utama Kartanegara');

// Sesuaikan default timezone
date_default_timezone_set('Asia/Jakarta');

// ============================================
// SISTEM AUTO LOGOUT (SESSION TIMEOUT)
// Seberapa lama user dibolehkan "idle" (tak ada aktivitas)
// dalam hitungan detik. Misal: 1800 detik = 30 menit
// ============================================
define('SESSION_TIMEOUT_SECONDS', 1800);

if (isset($_SESSION['user_id'])) {
    // Backward compatibility: Set login_time jika user belum logout sejak fitur ini ditambahkan
    if (!isset($_SESSION['login_time'])) {
        $_SESSION['login_time'] = isset($_SESSION['LAST_ACTIVITY']) ? $_SESSION['LAST_ACTIVITY'] : time();
    }

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT_SECONDS)) {
        // Jika aktivitas terakhir sudah melebihi batas waktu
        session_unset();     // Kosongkan variabel $_SESSION
        session_destroy();   // Hancurkan file log session

        // Simpan pesan error ke session baru agar terbaca di halaman login
        session_start();
        $_SESSION['error_msg'] = "Sesi Anda telah berakhir karena tidak ada aktivitas (" . (SESSION_TIMEOUT_SECONDS / 60) . " menit). Silakan login kembali.";

        // Lempar kembali ke login
        header("Location: " . BASE_URL . '/modules/auth/login.php');
        exit;
    }
    // Update cap waktu aktivitas terakhir dengan waktu saat ini (tiap kali me-reload/mengklik page)
    $_SESSION['LAST_ACTIVITY'] = time();
}
