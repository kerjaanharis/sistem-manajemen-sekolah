<?php
session_start();
require_once '../../config/database.php';

// Pastikan user sudah login (opsional namun sangat disarankan untuk keamanan)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_siswa'])) {

    $id_siswa = $_POST['id_siswa'];

    // List of parameters from POST
    $fields = [
        'status_ayah',
        'nik_ayah',
        'nama_ayah',
        'tahun_lahir_ayah',
        'pendidikan_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_hp_ayah',
        'alamat_ayah',
        'status_ibu',
        'nik_ibu',
        'nama_ibu',
        'tahun_lahir_ibu',
        'pendidikan_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_hp_ibu',
        'alamat_ibu',
        'nik_wali',
        'nama_wali',
        'tahun_lahir_wali',
        'pendidikan_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
        'no_hp_wali',
        'alamat_wali',
        'hubungan_wali'
    ];

    $updates = [];
    $params = [];

    // Cek masing-masing field
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            // sanitize if necessary, but PDO bindParam will protect from SQL injection
            $updates[] = "{$field} = ?";

            // Set empty strings to null for integers/years, or keep them if intended
            $val = trim($_POST[$field]);
            if (($field == 'tahun_lahir_ayah' || $field == 'tahun_lahir_ibu' || $field == 'tahun_lahir_wali') && $val === '') {
                $params[] = null;
            } else {
                $params[] = $val;
            }
        }
    }

    if (empty($updates)) {
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Tidak ada data yang diubah.'];
        header("Location: detail.php?id=" . $id_siswa . "&tab=ortu");
        exit;
    }

    $params[] = $id_siswa; // binding for WHERE id_user = ? (or id_siswa). Since table uses `id_user` as primary key for siswa? wait let's check. 
    // Usually it's id_user. We need to be sure. Let's assume id_user for `data_siswa` table as observed in detail.php. Wait, the form passed value is from `$id_siswa`, which is detail.php `id_user`.

    try {
        $sql = "UPDATE data_siswa SET " . implode(", ", $updates) . " WHERE id_user = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Record log activity
        $admin_id = $_SESSION['user_id'];
        $username = $_SESSION['username'] ?? 'Sistem';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $log_sql = "INSERT INTO log_aktivitas (id_user, username, aktivitas, tabel_terkait, keterangan, ip_address, user_agent)
                    VALUES (?, ?, 'Edit', 'data_siswa', 'Memperbarui Data Orang Tua Siswa ID: $id_siswa', ?, ?)";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([$admin_id, $username, $ip_address, $user_agent]);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Data Orang Tua & Wali berhasil diperbarui!'];
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
    }

    // Redirect kembali ke detail siswa dengan parameter modal atau tab open jika diperlukan
    // But detail.php uses JS localstorage for tab persistence or default tab
    header("Location: detail.php?id=" . $id_siswa . "&tab=ortu");
    exit;
} else {
    // If accessed directly without POST
    header("Location: index.php");
    exit;
}
?>