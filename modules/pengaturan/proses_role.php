<?php
// proses_role.php
session_start();
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    http_response_code(403);
    $_SESSION['error_msg'] = "Unauthorized access.";
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_role = isset($_POST['id_role']) ? (int) $_POST['id_role'] : 0;

    // Keamanan: Jangan biarkan admin mengedit role-nya sendiri (Role_ID = 1) untuk mencegah terkunci
    if ($id_role == 1) {
        $_SESSION['error_msg'] = "Hak akses administrator utama tidak dapat diubah.";
        header("Location: " . base_url('modules/pengaturan/index.php?tab=role&role_id=1'));
        exit;
    }

    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    try {
        $pdo->beginTransaction();

        // Ambil nama role untuk keperluan log
        $stmt_role = $pdo->prepare("SELECT nama_role FROM roles WHERE id_role = ?");
        $stmt_role->execute([$id_role]);
        $role_name = $stmt_role->fetchColumn() ?: "Unknown Role";

        // Iterasi melalui semua permissions yang dikirim dari form
        foreach ($permissions as $modul_id => $perms) {
            $can_view = isset($perms['can_view']) ? 1 : 0;
            $can_add = isset($perms['can_add']) ? 1 : 0;
            $can_edit = isset($perms['can_edit']) ? 1 : 0;
            $can_delete = isset($perms['can_delete']) ? 1 : 0;

            // Upsert permission record (Insert jika belum ada, Update jika sudah ada)
            $sql = "INSERT INTO role_permissions (id_role, id_modul, can_view, can_add, can_edit, can_delete)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    can_view = VALUES(can_view),
                    can_add = VALUES(can_add),
                    can_edit = VALUES(can_edit),
                    can_delete = VALUES(can_delete)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_role, $modul_id, $can_view, $can_add, $can_edit, $can_delete]);
        }

        $pdo->commit();

        catat_log($pdo, 'Update Hak Akses', "Memperbarui izin modul untuk role: " . $role_name);
        $_SESSION['success_msg'] = "Hak akses untuk role '{$role_name}' berhasil disimpan!";

        // Redirect kembali ke halaman dengan role yang sama
        header("Location: " . base_url('modules/pengaturan/index.php?tab=role&role_id=' . $id_role));
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Gagal menyimpan hak akses: " . $e->getMessage();
        header("Location: " . base_url('modules/pengaturan/index.php?tab=role&role_id=' . $id_role));
        exit;
    }
} else {
    $_SESSION['error_msg'] = "Invalid Request Method.";
    header("Location: " . base_url('modules/pengaturan/index.php?tab=role'));
    exit;
}
?>