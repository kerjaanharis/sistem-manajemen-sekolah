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

// Set maximum execution time and memory for large DB operations
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {

    $aksi = $_POST['aksi'];

    try {
        if ($aksi === 'backup') {

            // Generate nama file
            $TahunAjaran = defined('TAHUN_AJARAN') ? str_replace('/', '-', TAHUN_AJARAN) : '2024-2025';
            $date = date('Y-m-d_H-i-s');
            $filename = "Backup_Pintu_Kartanegara_{$TahunAjaran}_{$date}.sql";

            // Inisialisasi string SQL
            $sqlScript = "-- ==========================================================\n";
            $sqlScript .= "-- Backup Database Sister PINTU KARTANEGARA\n";
            $sqlScript .= "-- Waktu Backup: " . date('Y-m-d H:i:s') . "\n";
            $sqlScript .= "-- Oleh: " . $_SESSION['nama_lengkap'] . " (" . $_SESSION['username'] . ")\n";
            $sqlScript .= "-- ==========================================================\n\n";

            $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sqlScript .= "START TRANSACTION;\n\n";

            // Dapatkan semua tabel
            $tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                // Drop table jika ada
                $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";

                // Create table
                $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                $row = $stmt->fetch(PDO::FETCH_NUM);
                $sqlScript .= $row[1] . ";\n\n";

                // Ambil data
                $stmt = $pdo->query("SELECT * FROM `$table`");
                $rowCount = $stmt->rowCount();

                if ($rowCount > 0) {
                    $sqlScript .= "INSERT INTO `$table` VALUES\n";
                    $values = [];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $rowValues[] = "NULL";
                            } else {
                                $rowValues[] = $pdo->quote($value);
                            }
                        }
                        $values[] = "(" . implode(", ", $rowValues) . ")";
                    }
                    $sqlScript .= implode(",\n", $values) . ";\n\n";
                }
            }

            $sqlScript .= "COMMIT;\n";
            $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

            catat_log($pdo, 'Backup DB', "Melakukan unduh backup database sistem.");

            // Output ke browser untuk diunduh
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo $sqlScript;
            exit;

        } elseif ($aksi === 'restore') {

            if (!isset($_FILES['file_backup']) || $_FILES['file_backup']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Gagal mengunggah file backup. Pastikan file valid.");
            }

            $file_ext = strtolower(pathinfo($_FILES['file_backup']['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'sql') {
                throw new Exception("Format file harus .sql");
            }

            $sqlContent = file_get_contents($_FILES['file_backup']['tmp_name']);
            if (empty($sqlContent)) {
                throw new Exception("File sql kosong atau tidak terbaca.");
            }

            $pdo->beginTransaction();
            try {
                // Nonaktifkan FK checks saat restore
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

                // Eksekusi semua query. Karena ini file yg di-generate sistem kita sendiri (biasanya aman).
                // note: Untuk file besar, `exec` PDO dengan string raksasa didukung MySQL, 
                // asalkan tidak melebih max_allowed_packet.
                $pdo->exec($sqlContent);

                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                $pdo->commit();

                catat_log($pdo, 'Restore DB', "Sistem berhasil dipulihkan dari file backup: " . $_FILES['file_backup']['name']);
                $_SESSION['success_msg'] = "Database berhasil dipulihkan secara penuh.";

            } catch (PDOException $e) {
                $pdo->rollBack();
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                throw new Exception("Kesalahan Query SQL: " . $e->getMessage());
            }

        } elseif ($aksi === 'reset') {

            $current_user_id = (int) $_SESSION['user_id'];

            $pdo->beginTransaction();
            try {
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

                $tables = [];
                $stmt = $pdo->query("SHOW TABLES");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }

                foreach ($tables as $table) {
                    // Jangan sentuh tabel roles (master role)
                    // dan master_tahun_ajaran (pengaturan TA berjalan)
                    if ($table === 'roles' || $table === 'master_tahun_ajaran')
                        continue;

                    if ($table === 'users') {
                        // Hapus semua user kecuali admin yang sedang login
                        $stmt_del = $pdo->prepare("DELETE FROM users WHERE id_user != ?");
                        $stmt_del->execute([$current_user_id]);
                    } elseif ($table === 'log_aktivitas') {
                        // Kosongkan log
                        $pdo->exec("TRUNCATE TABLE `$table`");
                    } else {
                        // Truncate tabel transaksi/master lainnya
                        $pdo->exec("TRUNCATE TABLE `$table`");
                    }
                }

                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                $pdo->commit();

                // Catat log *setelah* reset (agar log pertama setelah reset adalah ini)
                catat_log($pdo, 'Reset DB', "Melakukan Reset (Pengosongan) seluruh data transaksi dan operasional sistem.");

                $_SESSION['success_msg'] = "Sistem berhasil di-reset. Semua histori operasional, log, dan pengguna (kecuali akun Anda) telah dibersihkan.";

            } catch (PDOException $e) {
                $pdo->rollBack();
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                throw new Exception("Gagal mereset database: " . $e->getMessage());
            }

        } else {
            throw new Exception("Aksi tidak valid.");
        }

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }

    header("Location: " . base_url('modules/database/index.php'));
    exit;

} else {
    header("Location: " . base_url('modules/database/index.php'));
    exit;
}
