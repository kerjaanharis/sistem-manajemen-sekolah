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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_siswa'])) {
    $id_siswa = $_POST['id_siswa'];

    // Ambil nama siswa untuk log
    $stmt_siswa = $pdo->prepare("SELECT nama_lengkap FROM data_siswa WHERE id_siswa = ?");
    $stmt_siswa->execute([$id_siswa]);
    $nama_siswa = $stmt_siswa->fetchColumn();

    if (!$nama_siswa) {
        $_SESSION['error_msg'] = "ID Siswa tidak valid.";
        header("Location: " . base_url('modules/siswa/index.php'));
        exit;
    }

    // Tangkap data POST alamat
    $alamat_lengkap = $_POST['alamat_lengkap'] ?? null;
    $rt = $_POST['rt'] ?? null;
    $rw = $_POST['rw'] ?? null;
    $dusun = $_POST['dusun'] ?? null;
    $desa_kelurahan = $_POST['desa_kelurahan'] ?? null;
    $kecamatan = $_POST['kecamatan'] ?? null;
    $kota_kabupaten = $_POST['kota_kabupaten'] ?? null;
    $provinsi = $_POST['provinsi'] ?? null;
    $kode_pos = $_POST['kode_pos'] ?? null;
    $lintang = $_POST['lintang'] ?? null;
    $bujur = $_POST['bujur'] ?? null;

    try {
        $stmt = $pdo->prepare("UPDATE data_siswa SET 
            alamat_lengkap = ?,
            rt = ?,
            rw = ?,
            dusun = ?,
            desa_kelurahan = ?,
            kecamatan = ?,
            kota_kabupaten = ?,
            provinsi = ?,
            kode_pos = ?,
            lintang = ?,
            bujur = ?
            WHERE id_siswa = ?");

        $stmt->execute([
            $alamat_lengkap,
            $rt,
            $rw,
            $dusun,
            $desa_kelurahan,
            $kecamatan,
            $kota_kabupaten,
            $provinsi,
            $kode_pos,
            $lintang,
            $bujur,
            $id_siswa
        ]);

        $_SESSION['success_msg'] = "Data Alamat Siswa berhasil diperbarui!";

        // Catat Log
        catat_log($pdo, 'Edit Alamat Siswa', "Memperbarui data alamat lengkap dan kordinat rumah untuk siswa {$nama_siswa}");

    } catch (\PDOException $e) {
        $_SESSION['error_msg'] = "Gagal memperbarui opsi alamat: " . $e->getMessage();
    }

    // Redirect kembali ke detail.php pada tab alamat
    header("Location: " . base_url("modules/siswa/detail.php?id={$id_siswa}&tab=alamat"));
    exit;
}

// Redirect fallback
header("Location: " . base_url('modules/siswa/index.php'));
exit;
