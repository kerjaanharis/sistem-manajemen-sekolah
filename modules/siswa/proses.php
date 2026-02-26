<?php
session_start();
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('modules/auth/login.php'));
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $nis = sanitize($_POST['nis'] ?? '');
    $nisn = sanitize($_POST['nisn'] ?? null);
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $jenis_kelamin = sanitize($_POST['jenis_kelamin'] ?? 'L');
    $kelas = sanitize($_POST['kelas'] ?? '');
    $angkatan = sanitize($_POST['angkatan'] ?? null);
    $rfid_tag = !empty($_POST['rfid_tag']) ? sanitize($_POST['rfid_tag']) : null;
    $create_account = isset($_POST['create_account']) ? true : false;

    // Default Role Siswa (dari setup db_init.sql, role Siswa ID-nya 5)
    $role_siswa_id = 5;

    if (empty($nis) || empty($nama_lengkap) || empty($kelas)) {
        $_SESSION['error_msg'] = "NIS, Nama Lengkap, dan Kelas wajib diisi.";
        header("Location: tambah.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $id_user = null;

        // Pembuatan akun SSO jika dicentang
        if ($create_account) {
            // Cek apakah username (nis) sudah dipakai user lain
            $stmt_check_user = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt_check_user->execute(['username' => $nis]);
            if ($stmt_check_user->rowCount() > 0) {
                throw new Exception("Gagal membuat akun SSO, username/NIS '$nis' sudah terdaftar dalam sistem.");
            }

            // Password default sama dengan NIS
            $password_hash = password_hash($nis, PASSWORD_DEFAULT);

            $stmt_user = $pdo->prepare("INSERT INTO users (username, password_hash, id_role) VALUES (?, ?, ?)");
            $stmt_user->execute([$nis, $password_hash, $role_siswa_id]);
            $id_user = $pdo->lastInsertId();
        }

        // Simpan data ke tabel data_siswa
        // Cek NIS Duplicate dulu
        $stmt_check_nis = $pdo->prepare("SELECT id_siswa FROM data_siswa WHERE nis = :nis");
        $stmt_check_nis->execute(['nis' => $nis]);
        if ($stmt_check_nis->rowCount() > 0) {
            throw new Exception("Gagal menyimpan, NIS '$nis' sudah terdaftar di data siswa.");
        }

        $stmt_siswa = $pdo->prepare("INSERT INTO data_siswa (id_user, nis, nisn, nama_lengkap, jenis_kelamin, kelas, angkatan, rfid_tag) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_siswa->execute([$id_user, $nis, $nisn, $nama_lengkap, $jenis_kelamin, $kelas, $angkatan, $rfid_tag]);

        // Log Aktivitas
        catat_log($pdo, 'Tambah', "Menambahkan data siswa baru: $nama_lengkap (NIS: $nis)");

        $pdo->commit();
        $_SESSION['success_msg'] = "Data siswa $nama_lengkap berhasil ditambahkan" . ($create_account ? " beserta hak akses SSO-nya." : ".");
        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi Kesalahan: " . $e->getMessage();
        header("Location: tambah.php");
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
    $id_user_existing = !empty($_POST['id_user']) ? (int) $_POST['id_user'] : null;

    $nis = sanitize($_POST['nis'] ?? '');
    $nisn = sanitize($_POST['nisn'] ?? null);
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $jenis_kelamin = sanitize($_POST['jenis_kelamin'] ?? 'L');
    $kelas = sanitize($_POST['kelas'] ?? '');
    $angkatan = sanitize($_POST['angkatan'] ?? null);
    $rfid_tag = !empty($_POST['rfid_tag']) ? sanitize($_POST['rfid_tag']) : null;
    $status_siswa = sanitize($_POST['status_siswa'] ?? 'Aktif');
    $create_account = isset($_POST['create_account']) ? true : false;

    $role_siswa_id = 5;

    try {
        $pdo->beginTransaction();

        $id_user = $id_user_existing;

        // Buat akun baru jika diminta dan belum punya
        if (!$id_user_existing && $create_account) {
            $stmt_check_user = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt_check_user->execute(['username' => $nis]);
            if ($stmt_check_user->rowCount() > 0) {
                throw new Exception("Gagal membuat akun SSO, username/NIS '$nis' sudah terdaftar oleh pengguna lain.");
            }

            $password_hash = password_hash($nis, PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (username, password_hash, id_role) VALUES (?, ?, ?)");
            $stmt_user->execute([$nis, $password_hash, $role_siswa_id]);
            $id_user = $pdo->lastInsertId();
        }

        // Cek duplicate NIS pada saat update (kecuali milik sendiri)
        $stmt_dup = $pdo->prepare("SELECT id_siswa FROM data_siswa WHERE nis = :nis AND id_siswa != :id_siswa");
        $stmt_dup->execute(['nis' => $nis, 'id_siswa' => $id_siswa]);
        if ($stmt_dup->rowCount() > 0) {
            throw new Exception("Gagal menyimpan, NIS '$nis' sudah dipakai oleh siswa lain.");
        }

        // Ambil data sebelum diubah
        $stmt_old = $pdo->prepare("SELECT nis, nisn, nama_lengkap, jenis_kelamin, kelas, angkatan, rfid_tag, status_siswa FROM data_siswa WHERE id_siswa = ?");
        $stmt_old->execute([$id_siswa]);
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        // Update Data Siswa
        $stmt_siswa = $pdo->prepare("UPDATE data_siswa SET 
            id_user = ?, nis = ?, nisn = ?, nama_lengkap = ?, jenis_kelamin = ?, 
            kelas = ?, angkatan = ?, rfid_tag = ?, status_siswa = ? 
            WHERE id_siswa = ?");
        $stmt_siswa->execute([$id_user, $nis, $nisn, $nama_lengkap, $jenis_kelamin, $kelas, $angkatan, $rfid_tag, $status_siswa, $id_siswa]);

        // Format data perubahan
        $new_data = [
            'nis' => $nis,
            'nisn' => $nisn,
            'nama_lengkap' => $nama_lengkap,
            'jenis_kelamin' => $jenis_kelamin,
            'kelas' => $kelas,
            'angkatan' => $angkatan,
            'rfid_tag' => $rfid_tag,
            'status_siswa' => $status_siswa
        ];

        $perubahan = [];
        if ($old_data) {
            foreach ($old_data as $key => $val) {
                if ($val != $new_data[$key]) {
                    $val_show = empty($val) ? '(kosong)' : $val;
                    $new_show = empty($new_data[$key]) ? '(kosong)' : $new_data[$key];
                    $perubahan[] = strtoupper($key) . ": '$val_show' ➔ '$new_show'";
                }
            }
        }
        $detail_log = empty($perubahan) ? "Tidak ada atribut yang diubah." : "Perubahan Detail:\n- " . implode("\n- ", $perubahan);

        // Log Aktivitas
        catat_log($pdo, 'Edit', "Memperbarui profil siswa: $nama_lengkap\n\n$detail_log");


        $pdo->commit();
        $_SESSION['success_msg'] = "Profil siswa $nama_lengkap berhasil diperbarui.";
        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi Kesalahan: " . $e->getMessage();
        header("Location: edit.php?id=$id_siswa");
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && $action == 'delete') {
    $id_siswa = (int) ($_GET['id'] ?? 0);

    try {
        $pdo->beginTransaction();

        // Ambil data user ID yang berafiliasi untuk dihapus dari SSO
        $stmt = $pdo->prepare("SELECT nama_lengkap, id_user FROM data_siswa WHERE id_siswa = ?");
        $stmt->execute([$id_siswa]);
        $siswa = $stmt->fetch();

        if ($siswa) {
            $nama = $siswa['nama_lengkap'];

            // Hapus data_siswa
            $stmt_del = $pdo->prepare("DELETE FROM data_siswa WHERE id_siswa = ?");
            $stmt_del->execute([$id_siswa]);

            // Hapus users SSO jika ada
            if ($siswa['id_user']) {
                $stmt_usr = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
                $stmt_usr->execute([$siswa['id_user']]);
            }

            // Log Aktivitas
            catat_log($pdo, 'Hapus', "Menghapus permanen data siswa: $nama");

            $pdo->commit();
            $_SESSION['success_msg'] = "Data profil dan akun SSO siswa $nama berhasil dihapus permanen.";
        }

        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Gagal menghapus data: Ada relasi sistem lain yang menggunakan data ini. (" . $e->getMessage() . ")";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
