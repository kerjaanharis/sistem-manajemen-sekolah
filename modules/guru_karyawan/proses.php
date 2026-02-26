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

// Mapping ID Role di SSO
// Guru = 3, Karyawan = 4
function getRoleIdByTipe($tipe)
{
    if ($tipe == 'Guru')
        return 3;
    if ($tipe == 'Karyawan')
        return 4;
    return 4; // Default Karyawan
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $nip_nik = sanitize($_POST['nip_nik'] ?? '');
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $tipe_pegawai = sanitize($_POST['tipe_pegawai'] ?? 'Guru');
    $jabatan = sanitize($_POST['jabatan'] ?? null);
    $rfid_tag = !empty($_POST['rfid_tag']) ? sanitize($_POST['rfid_tag']) : null;
    $create_account = isset($_POST['create_account']) ? true : false;

    $role_id = getRoleIdByTipe($tipe_pegawai);

    if (empty($nip_nik) || empty($nama_lengkap) || empty($tipe_pegawai)) {
        $_SESSION['error_msg'] = "NIP/NIK, Nama Lengkap, dan Tipe Pegawai wajib diisi.";
        header("Location: tambah.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $id_user = null;

        // Pembuatan akun SSO jika dicentang
        if ($create_account) {
            $stmt_check_user = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt_check_user->execute(['username' => $nip_nik]);
            if ($stmt_check_user->rowCount() > 0) {
                throw new Exception("Username/NIP '$nip_nik' sudah terdaftar sebagai akun SSO.");
            }

            $password_hash = password_hash($nip_nik, PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (username, password_hash, id_role) VALUES (?, ?, ?)");
            $stmt_user->execute([$nip_nik, $password_hash, $role_id]);
            $id_user = $pdo->lastInsertId();
        }

        // Cek NIS Duplicate dulu
        $stmt_check = $pdo->prepare("SELECT id_pegawai FROM data_pegawai WHERE nip_nik = :nip");
        $stmt_check->execute(['nip' => $nip_nik]);
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Gagal menyimpan, NIP/NIK '$nip_nik' sudah terdaftar.");
        }

        $stmt_pegawai = $pdo->prepare("INSERT INTO data_pegawai (id_user, nip_nik, nama_lengkap, tipe_pegawai, jabatan, rfid_tag) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_pegawai->execute([$id_user, $nip_nik, $nama_lengkap, $tipe_pegawai, $jabatan, $rfid_tag]);

        // Log Aktivitas
        catat_log($pdo, 'Tambah', "Menambahkan data pegawai baru: $nama_lengkap (NIP/NIK: $nip_nik)");

        $pdo->commit();
        $_SESSION['success_msg'] = "Data pegawai $nama_lengkap berhasil ditambahkan" . ($create_account ? " beserta hak akses SSO-nya." : ".");
        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi Kesalahan: " . $e->getMessage();
        header("Location: tambah.php");
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id_pegawai = (int) ($_POST['id_pegawai'] ?? 0);
    $id_user_existing = !empty($_POST['id_user']) ? (int) $_POST['id_user'] : null;

    $nip_nik = sanitize($_POST['nip_nik'] ?? '');
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $tipe_pegawai = sanitize($_POST['tipe_pegawai'] ?? 'Guru');
    $jabatan = sanitize($_POST['jabatan'] ?? null);
    $rfid_tag = !empty($_POST['rfid_tag']) ? sanitize($_POST['rfid_tag']) : null;
    $status_pegawai = sanitize($_POST['status_pegawai'] ?? 'Aktif');
    $create_account = isset($_POST['create_account']) ? true : false;

    $role_id = getRoleIdByTipe($tipe_pegawai);

    try {
        $pdo->beginTransaction();

        $id_user = $id_user_existing;

        // Update SSO Role jika sudah punya akun
        if ($id_user_existing) {
            $stmt_update_role = $pdo->prepare("UPDATE users SET id_role = ? WHERE id_user = ?");
            $stmt_update_role->execute([$role_id, $id_user_existing]);
        }
        // Buat akun baru jika diminta dan belum punya
        elseif ($create_account) {
            $stmt_check_user = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt_check_user->execute(['username' => $nip_nik]);
            if ($stmt_check_user->rowCount() > 0) {
                throw new Exception("Username '$nip_nik' sudah terdaftar.");
            }

            $password_hash = password_hash($nip_nik, PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (username, password_hash, id_role) VALUES (?, ?, ?)");
            $stmt_user->execute([$nip_nik, $password_hash, $role_id]);
            $id_user = $pdo->lastInsertId();
        }

        // Cek duplicate NIP
        $stmt_dup = $pdo->prepare("SELECT id_pegawai FROM data_pegawai WHERE nip_nik = :nip AND id_pegawai != :id_pegawai");
        $stmt_dup->execute(['nip' => $nip_nik, 'id_pegawai' => $id_pegawai]);
        if ($stmt_dup->rowCount() > 0) {
            throw new Exception("Gagal menyimpan, NIP/NIK '$nip_nik' sudah dipakai pegawai lain.");
        }

        // Ambil data sebelum diubah
        $stmt_old = $pdo->prepare("SELECT nip_nik, nama_lengkap, tipe_pegawai, jabatan, rfid_tag, status_pegawai FROM data_pegawai WHERE id_pegawai = ?");
        $stmt_old->execute([$id_pegawai]);
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        // Update Data Pegawai
        $stmt_pegawai = $pdo->prepare("UPDATE data_pegawai SET 
            id_user = ?, nip_nik = ?, nama_lengkap = ?, tipe_pegawai = ?, jabatan = ?, 
            rfid_tag = ?, status_pegawai = ? 
            WHERE id_pegawai = ?");
        $stmt_pegawai->execute([$id_user, $nip_nik, $nama_lengkap, $tipe_pegawai, $jabatan, $rfid_tag, $status_pegawai, $id_pegawai]);

        // Format data perubahan
        $new_data = [
            'nip_nik' => $nip_nik,
            'nama_lengkap' => $nama_lengkap,
            'tipe_pegawai' => $tipe_pegawai,
            'jabatan' => $jabatan,
            'rfid_tag' => $rfid_tag,
            'status_pegawai' => $status_pegawai
        ];

        $perubahan = [];
        if ($old_data) {
            foreach ($old_data as $key => $val) {
                if ($val != $new_data[$key]) {
                    $val_show = empty($val) ? '(kosong)' : $val;
                    $new_show = empty($new_data[$key]) ? '(kosong)' : $new_data[$key];
                    $perubahan[] = strtoupper(str_replace('_', ' ', $key)) . ": '$val_show' ➔ '$new_show'";
                }
            }
        }
        $detail_log = empty($perubahan) ? "Tidak ada atribut spesifik yang diubah." : "Perubahan Detail:\n- " . implode("\n- ", $perubahan);

        // Log Aktivitas
        catat_log($pdo, 'Edit', "Memperbarui info pegawai: $nama_lengkap\n\n$detail_log");


        $pdo->commit();
        $_SESSION['success_msg'] = "Profil pegawai $nama_lengkap berhasil diperbarui.";
        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi Kesalahan: " . $e->getMessage();
        header("Location: edit.php?id=$id_pegawai");
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && $action == 'delete') {
    $id_pegawai = (int) ($_GET['id'] ?? 0);

    try {
        $pdo->beginTransaction();

        // Ambil data
        $stmt = $pdo->prepare("SELECT nama_lengkap, id_user FROM data_pegawai WHERE id_pegawai = ?");
        $stmt->execute([$id_pegawai]);
        $pegawai = $stmt->fetch();

        if ($pegawai) {
            $nama = $pegawai['nama_lengkap'];

            // Hapus pegawai
            $stmt_del = $pdo->prepare("DELETE FROM data_pegawai WHERE id_pegawai = ?");
            $stmt_del->execute([$id_pegawai]);

            // Hapus users
            if ($pegawai['id_user']) {
                $stmt_usr = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
                $stmt_usr->execute([$pegawai['id_user']]);
            }

            // Log
            catat_log($pdo, 'Hapus', "Menghapus permanen data pegawai: $nama");

            $pdo->commit();
            $_SESSION['success_msg'] = "Data profil pegawai $nama berhasil dihapus.";
        }

        header("Location: index.php");
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Gagal menghapus data: Data dikunci karena berafiliasi dengan riwayat sistem. (" . $e->getMessage() . ")";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
