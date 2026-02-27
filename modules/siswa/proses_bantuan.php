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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    $id_siswa = $_POST['id_siswa'] ?? '';

    if (!$id_siswa) {
        $_SESSION['error_msg'] = "ID Siswa tidak valid.";
        header("Location: " . base_url('modules/siswa/index.php'));
        exit;
    }

    // Ambil nama siswa untuk log
    $stmt_siswa = $pdo->prepare("SELECT nama_lengkap FROM data_siswa WHERE id_siswa = ?");
    $stmt_siswa->execute([$id_siswa]);
    $nama_siswa = $stmt_siswa->fetchColumn() ?: "Siswa";

    if ($aksi == 'tambah_bantuan') {
        $jenis_bantuan = $_POST['jenis_bantuan'] ?? '';
        $tahun_diterima = $_POST['tahun_diterima'] ?? '';
        $periode = $_POST['periode'] ?? '';
        $tanggal_penerimaan = !empty($_POST['tanggal_penerimaan']) ? $_POST['tanggal_penerimaan'] : null;
        $nominal = !empty($_POST['nominal']) ? str_replace('.', '', $_POST['nominal']) : 0;
        $keterangan = $_POST['keterangan'] ?? '';

        if (empty($jenis_bantuan)) {
            $_SESSION['error_msg'] = "Jenis Bantuan wajib diisi!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO siswa_bantuan (id_siswa, jenis_bantuan, tahun_diterima, periode, tanggal_penerimaan, nominal, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_siswa, $jenis_bantuan, $tahun_diterima, $periode, $tanggal_penerimaan, $nominal, $keterangan]);
                $_SESSION['success_msg'] = "Data Catatan Bantuan berhasil ditambahkan!";
                catat_log($pdo, 'Tambah Bantuan Siswa', "Menambahkan bantuan {$jenis_bantuan} untuk siswa {$nama_siswa}");
            } catch (\PDOException $e) {
                $_SESSION['error_msg'] = "Gagal menyimpan data bantuan: " . $e->getMessage();
            }
        }
    } elseif ($aksi == 'edit_bantuan') {
        $id_bantuan = $_POST['id_bantuan'] ?? '';
        $jenis_bantuan = $_POST['jenis_bantuan'] ?? '';
        $tahun_diterima = $_POST['tahun_diterima'] ?? '';
        $periode = $_POST['periode'] ?? '';
        $tanggal_penerimaan = !empty($_POST['tanggal_penerimaan']) ? $_POST['tanggal_penerimaan'] : null;
        $nominal = !empty($_POST['nominal']) ? str_replace('.', '', $_POST['nominal']) : 0;
        $keterangan = $_POST['keterangan'] ?? '';

        if (empty($id_bantuan) || empty($jenis_bantuan)) {
            $_SESSION['error_msg'] = "ID atau Jenis Bantuan tidak valid!";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE siswa_bantuan SET jenis_bantuan = ?, tahun_diterima = ?, periode = ?, tanggal_penerimaan = ?, nominal = ?, keterangan = ? WHERE id_bantuan = ? AND id_siswa = ?");
                $stmt->execute([$jenis_bantuan, $tahun_diterima, $periode, $tanggal_penerimaan, $nominal, $keterangan, $id_bantuan, $id_siswa]);
                $_SESSION['success_msg'] = "Catatan Bantuan berhasil diperbarui!";
                catat_log($pdo, 'Edit Bantuan Siswa', "Mengubah data bantuan {$jenis_bantuan} siswa {$nama_siswa}");
            } catch (\PDOException $e) {
                $_SESSION['error_msg'] = "Gagal memperbarui catatan bantuan: " . $e->getMessage();
            }
        }
    } elseif ($aksi == 'hapus_bantuan') {
        $id_bantuan = $_POST['id_bantuan'] ?? '';
        if ($id_bantuan) {
            try {
                $stmt = $pdo->prepare("DELETE FROM siswa_bantuan WHERE id_bantuan = ? AND id_siswa = ?");
                $stmt->execute([$id_bantuan, $id_siswa]);
                $_SESSION['success_msg'] = "Catatan bantuan berhasil dihapus dari riwayat.";
                catat_log($pdo, 'Hapus Bantuan Siswa', "Menghapus catatan bantuan dari siswa {$nama_siswa}");
            } catch (\PDOException $e) {
                $_SESSION['error_msg'] = "Gagal menghapus catatan bantuan: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_msg'] = "ID Bantuan tidak valid.";
        }
    }

    // Redirect kembali ke detail.php pada tab bantuan
    header("Location: " . base_url("modules/siswa/detail.php?id={$id_siswa}"));
    exit;
}

// Jika bukan POST atau tidak ada aksi
header("Location: " . base_url('modules/siswa/index.php'));
exit;
?>