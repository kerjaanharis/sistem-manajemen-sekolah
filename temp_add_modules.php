<?php
require_once 'config/database.php';

try {
    $sql = "
    INSERT IGNORE INTO sys_modules (nama_modul, deskripsi) VALUES 
    ('Data Guru & Karyawan', 'Manajemen Direktori Tenaga Pendidik dan Karyawan'),
    ('Tugas Tambahan', 'Manajemen Jabatan dan Tugas Tambahan Pegawai'),
    ('Master Kelas', 'Pengaturan Data Kelas, Tingkat, Rombel, dan Wali Kelas'),
    ('Manajemen Database', 'Akses Backup, Restore, dan Reset Database'),
    ('Log Aktivitas', 'Melihat Riwayat Aktivitas Pengguna (Log Sistem)');
    ";

    $pdo->exec($sql);
    echo "TAMBAHAN MODULES SEEDED\n";

    // Beri Admin akses penuh ke modul baru
    $sql_admin = "
    INSERT IGNORE INTO role_permissions (id_role, id_modul, can_view, can_add, can_edit, can_delete)
    SELECT 1, id_modul, 1, 1, 1, 1 FROM sys_modules;
    ";
    $pdo->exec($sql_admin);
    echo "ADMIN PERMISSIONS UPDATED\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>