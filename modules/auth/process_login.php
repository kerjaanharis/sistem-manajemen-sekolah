<?php
session_start();
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error_msg'] = "Username dan password wajib diisi!";
        header("Location: login.php");
        exit;
    }

    try {
        // Cek username di database
        $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u 
                               JOIN roles r ON u.id_role = r.id_role 
                               WHERE u.username = :username AND u.is_active = 1 LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Verifikasi password (menggunakan password_verify atau untuk dev sementara MD5/Plain, disini sy asumsikan password_hash() PHP)
        if ($user && password_verify($password, $user['password_hash'])) {

            // Ambil Nama Asli Berdasarkan Role
            $nama_lengkap = $user['username']; // fallback
            if (in_array($user['id_role'], [1, 2])) {
                // Admin / Pimpinan
                if ($user['username'] === 'admin')
                    $nama_lengkap = 'Administrator';
            } elseif (in_array($user['id_role'], [3, 4])) {
                // Guru / Karyawan
                $stmt_p = $pdo->prepare("SELECT nama_lengkap FROM data_pegawai WHERE id_user = ?");
                $stmt_p->execute([$user['id_user']]);
                if ($d = $stmt_p->fetch())
                    $nama_lengkap = $d['nama_lengkap'];
            } elseif ($user['id_role'] == 5) {
                // Siswa
                $stmt_s = $pdo->prepare("SELECT nama_lengkap FROM data_siswa WHERE id_user = ?");
                $stmt_s->execute([$user['id_user']]);
                if ($d = $stmt_s->fetch())
                    $nama_lengkap = $d['nama_lengkap'];
            }

            // Set Session Login
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['role_id'] = $user['id_role'];
            $_SESSION['role_name'] = $user['nama_role'];
            $_SESSION['login_time'] = time();

            // Rekam Log Login
            catat_log($pdo, 'Login', 'Login berhasil ke dalam sistem utama');

            // Update last_login
            $pdo->query("UPDATE users SET last_login = NOW() WHERE id_user = " . (int) $user['id_user']);

            // Redirect sesuai role atau dashboard utama
            header("Location: " . base_url());
            exit;

        } else {
            catat_log($pdo, 'Gagal Login', "Upaya login gagal dengan username/NIP: " . $username, $username, '-');
            $_SESSION['error_msg'] = "Username atau sandi salah, atau akun tidak aktif.";
            header("Location: login.php");
            exit;
        }
    } catch (\PDOException $e) {
        // Handle no table error during dev (apabila DB belum di setup)
        $_SESSION['error_msg'] = "Sistem gagal terhubung ke Database SSO (" . $e->getMessage() . "). Silakan hubungi Administrator.";
        header("Location: login.php");
        exit;
    }

} else {
    header("Location: login.php");
    exit;
}
