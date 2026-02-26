<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin yang bisa memproses
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    $_SESSION['error_msg'] = "Akses ditolak. Silakan login sebagai administrator.";
    header("Location: " . base_url('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {

    $aksi = $_POST['aksi'];

    try {
        if ($aksi === 'tambah') {
            $tahun = trim($_POST['tahun_ajaran'] ?? '');
            $semester = trim($_POST['semester'] ?? '');

            if (empty($tahun) || empty($semester)) {
                throw new Exception("Kolom Tahun Ajaran dan Semester wajib diisi!");
            }

            $stmt = $pdo->prepare("INSERT INTO master_tahun_ajaran (tahun_ajaran, semester, is_active) VALUES (?, ?, 0)");
            $stmt->execute([$tahun, $semester]);

            catat_log($pdo, 'Tambah Data Master', "Menambahkan pilihan Tahun Ajaran baru: $tahun ($semester)");
            $_SESSION['success_msg'] = "Tahun Ajaran $tahun ($semester) berhasil ditambahkan ke daftar master.";

        } elseif ($aksi === 'aktifkan') {

            $id_ta = (int) $_POST['id_ta'];

            // Ambil Info Tahun & Semester yang akan diaktifkan
            $stmt_info = $pdo->prepare("SELECT tahun_ajaran, semester FROM master_tahun_ajaran WHERE id_ta = ?");
            $stmt_info->execute([$id_ta]);
            $target = $stmt_info->fetch();

            if (!$target)
                throw new Exception("Data tidak ditemukan.");

            $pdo->beginTransaction();
            // 1. Matikan semua yang aktif
            $pdo->exec("UPDATE master_tahun_ajaran SET is_active = 0 WHERE is_active = 1");
            // 2. Hidupkan opsi yang dipilih
            $stmt_on = $pdo->prepare("UPDATE master_tahun_ajaran SET is_active = 1 WHERE id_ta = ?");
            $stmt_on->execute([$id_ta]);
            $pdo->commit();

            catat_log($pdo, 'Pindah Semester', "Mengganti fokus sistem aktif ke Tahun Ajaran {$target['tahun_ajaran']} Semester {$target['semester']}");
            $_SESSION['success_msg'] = "Berhasil! Sistem PINTU KARTANEGARA kini berjalan pada Tahun Ajaran {$target['tahun_ajaran']} Semester {$target['semester']}.";

        } elseif ($aksi === 'hapus') {

            $id_ta = (int) $_POST['id_ta'];

            // Tidak boleh hapus yang sedang aktif
            $stmt_check = $pdo->prepare("SELECT is_active, tahun_ajaran FROM master_tahun_ajaran WHERE id_ta = ?");
            $stmt_check->execute([$id_ta]);
            $cek = $stmt_check->fetch();

            if ($cek && $cek['is_active'] == 1) {
                throw new Exception("Tidak bisa menghapus semester yang sedang aktif berjalan!");
            }

            $stmt_del = $pdo->prepare("DELETE FROM master_tahun_ajaran WHERE id_ta = ? AND is_active = 0");
            $stmt_del->execute([$id_ta]);

            catat_log($pdo, 'Hapus Data Master', "Menghapus opsi pendaftaran Tahun Ajaran: {$cek['tahun_ajaran']}");
            $_SESSION['success_msg'] = "Data tahun ajaran berhasil dihapus dari daftar master.";

        } else {
            throw new Exception("Aksi tidak valid.");
        }

    } catch (\Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        // Cek duplikasi kunci (Duplicate entry)
        if (strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
            $_SESSION['error_msg'] = "Tahun Ajaran dan Semester tersebut sudah pernah didaftarkan sebelumnya.";
        } else {
            $_SESSION['error_msg'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: index.php");
    exit;

} else {
    // Jika diakses tidak via POST
    header("Location: index.php");
    exit;
}
