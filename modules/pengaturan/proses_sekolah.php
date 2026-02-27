<?php
session_start();
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data text dari POST
    $fields = [
        'nama_sekolah',
        'npsn',
        'nss',
        'jenjang',
        'status_sekolah',
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
        'alamat_lengkap',
        'rt',
        'rw',
        'dusun',
        'desa_kelurahan',
        'kecamatan',
        'kota_kabupaten',
        'provinsi',
        'kode_pos',
        'lintang',
        'bujur',
        'telepon',
        'hp',
        'email_utama',
        'email_alternatif',
        'website',
        'instagram',
        'facebook',
        'tiktok',
        'youtube'
    ];

    $sets = [];
    $params = [];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? null;
        if ($val !== null) {
            $sets[] = "$f = ?";
            $params[] = trim($val);
        }
    }

    // 2. Handle File Uploads
    $upload_dir = '../../assets/uploads/sekolah/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Ambil data lama agar bisa menghapus file gambar bekas yang ditimpa
    $stmt_old = $pdo->query("SELECT * FROM pengaturan_sekolah WHERE id = 1");
    $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC) ?: [];

    $file_fields = ['logo', 'kop', 'favicon', 'stempel', 'ttd_kepsek', 'foto_kepsek'];

    foreach ($file_fields as $file_field) {
        if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES[$file_field]['tmp_name'];
            $name = basename($_FILES[$file_field]['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // Validasi extension gambar
            $allowed = ['jpg', 'jpeg', 'png'];
            if ($file_field === 'favicon') {
                $allowed[] = 'ico';
            }

            if (in_array($ext, $allowed)) {
                $new_filename = $file_field . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $sets[] = "$file_field = ?";
                    $params[] = $new_filename;

                    // Hapus file lama jika ada
                    if (!empty($old_data[$file_field]) && file_exists($upload_dir . $old_data[$file_field])) {
                        unlink($upload_dir . $old_data[$file_field]);
                    }
                }
            }
        }
    }

    // 3. Simpan ke database
    if (!empty($sets)) {
        $sql = "UPDATE pengaturan_sekolah SET " . implode(', ', $sets) . " WHERE id = 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Tentukan Log Dinamis
            $log_action = 'Update Data Sekolah';
            $log_detail = 'Memperbarui profil sekolah';

            if (isset($_POST['nama_sekolah'])) {
                $log_detail = 'Memperbarui Identitas Sekolah Utama (Nama, NPSN, KS)';
            } elseif (isset($_POST['alamat_lengkap'])) {
                $log_detail = 'Memperbarui Koordinat dan Alamat Sekolah';
            } elseif (isset($_POST['telepon'])) {
                $log_detail = 'Memperbarui Kontak dan Sosial Media Sekolah';
            } elseif (!empty($_FILES)) {
                $log_action = 'Upload Kelengkapan Sekolah';
                $log_detail = 'Mengunggah aset kelengkapan (Logo, KOP, TTD, dll)';
            }

            catat_log($pdo, $log_action, $log_detail);
            $_SESSION['success_msg'] = "Data Berhasil Disimpan!";

            http_response_code(200);
            echo "OK";
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Database error: " . $e->getMessage();
        }
    } else {
        http_response_code(200);
        echo "No changes";
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>