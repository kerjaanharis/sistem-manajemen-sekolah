<?php
// Fungsi-fungsi helper terpadu

// ==========================================
// PENGAKTIFAN PENGATURAN GLOBAL SISTEM
// ==========================================
global $pdo;
if (isset($pdo)) {
    try {
        $stmt_settings = $pdo->query("SELECT tahun_ajaran, semester FROM master_tahun_ajaran WHERE is_active = 1 LIMIT 1");
        $aktif = $stmt_settings->fetch(PDO::FETCH_ASSOC);

        define('TAHUN_AJARAN', $aktif ? $aktif['tahun_ajaran'] : '2024/2025');
        define('SEMESTER_AKTIF', $aktif ? $aktif['semester'] : 'Ganjil');
    } catch (\PDOException $e) {
        // Fallback jika tabel belum ada saat instalasi awal
        define('TAHUN_AJARAN', '2024/2025');
        define('SEMESTER_AKTIF', 'Ganjil');
    }
} else {
    // Fallback jika $pdo belum dideklarasikan (sangat jarang terjadi jika struktur include benar)
    define('TAHUN_AJARAN', '2024/2025');
    define('SEMESTER_AKTIF', 'Ganjil');
}


function base_url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header("Location: " . base_url($path));
    exit;
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format tanggal standar
function format_tanggal($date)
{
    return date('d-m-Y', strtotime($date));
}

// Fungsi Pencatatan Log Terpadu
function catat_log($pdo, $aktifitas, $keterangan, $override_nama = null, $override_peran = null)
{
    $id_user = $_SESSION['user_id'] ?? null;
    $nama_user = $override_nama ?? ($_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'Tamu'));
    $peran = $override_peran ?? ($_SESSION['role_name'] ?? 'Sistem');

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Cek jika panjang keterangan lebih dari batas, kita handle dengan aman
    // (tipe TEXT MySQL secara normal cukup untuk ribuan karakter).

    $stmt = $pdo->prepare("INSERT INTO log_aktivitas 
        (id_user, peran, nama_user, aktifitas, keterangan_tambahan, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $id_user,
        $peran,
        $nama_user,
        $aktifitas,
        $keterangan,
        $ip_address,
        $user_agent
    ]);
}
