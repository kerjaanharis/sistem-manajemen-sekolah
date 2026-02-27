<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin atau kepsek
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role_name']), ['administrator', 'kepala sekolah'])) {
    $_SESSION['error_msg'] = "Akses ditolak. Anda tidak memiliki izin untuk mengelola Master Tugas Tambahan.";
    header("Location: " . base_url('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];

    try {
        if ($aksi === 'tambah_master') {
            $nama_tugas = trim($_POST['nama_tugas']);
            $kategori = trim($_POST['kategori']);

            if (empty($nama_tugas)) {
                throw new Exception("Nama Tugas tidak boleh kosong.");
            }

            // Cek duplikat
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM master_tugas WHERE nama_tugas = ?");
            $stmt_cek->execute([$nama_tugas]);
            if ($stmt_cek->fetchColumn() > 0) {
                throw new Exception("Jabatan '$nama_tugas' sudah ada di dalam database.");
            }

            $stmt = $pdo->prepare("INSERT INTO master_tugas (nama_tugas, kategori) VALUES (?, ?)");
            $stmt->execute([$nama_tugas, $kategori]);

            catat_log($pdo, 'Tambah Master Tugas', "Menambahkan tipe jabatan baru: $nama_tugas");
            $_SESSION['success_msg'] = "Jabatan $nama_tugas berhasil ditambahkan ke Master List.";

            // Redirect back but append the tab parameter
            header("Location: index.php?tab=master");
            exit;

        } elseif ($aksi === 'edit_master') {
            $id_tugas = (int) $_POST['id_tugas'];
            $nama_tugas = trim($_POST['nama_tugas']);
            $kategori = trim($_POST['kategori']);

            if (empty($nama_tugas) || empty($id_tugas)) {
                throw new Exception("ID dan Nama Tugas tidak boleh kosong.");
            }

            // Cek duplikat nama (kecuali dirinya sendiri)
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM master_tugas WHERE nama_tugas = ? AND id_tugas != ?");
            $stmt_cek->execute([$nama_tugas, $id_tugas]);
            if ($stmt_cek->fetchColumn() > 0) {
                throw new Exception("Jabatan '$nama_tugas' sudah ada di dalam database.");
            }

            $stmt_upd = $pdo->prepare("UPDATE master_tugas SET nama_tugas = ?, kategori = ? WHERE id_tugas = ?");
            $stmt_upd->execute([$nama_tugas, $kategori, $id_tugas]);

            catat_log($pdo, 'Edit Master Tugas', "Mengubah identitas tugas ID $id_tugas menjadi $nama_tugas");
            $_SESSION['success_msg'] = "Perubahan profil jabatan $nama_tugas berhasil disimpan.";

            header("Location: index.php?tab=master");
            exit;

        } elseif ($aksi === 'hapus_master') {
            $id_tugas = (int) $_POST['id_tugas'];

            // Ambil nama untuk log
            $stmt_nama = $pdo->prepare("SELECT nama_tugas FROM master_tugas WHERE id_tugas = ?");
            $stmt_nama->execute([$id_tugas]);
            $nama_tugas = $stmt_nama->fetchColumn();

            $stmt_del = $pdo->prepare("DELETE FROM master_tugas WHERE id_tugas = ?");
            $stmt_del->execute([$id_tugas]); // ON DELETE CASCADE akan otomatis menghapus riwayat di penugasan_pegawai

            catat_log($pdo, 'Hapus Master Tugas', "Menghapus identitas tugas: $nama_tugas");
            $_SESSION['success_msg'] = "Jabatan $nama_tugas dan seluruh riwayatnya berhasil dihapus secara permanen.";

            header("Location: index.php?tab=master");
            exit;

        } elseif ($aksi === 'simpan_penugasan_massal') {
            $tahun_ajaran = trim($_POST['tahun_ajaran']);
            $semester = trim($_POST['semester']);
            $tugas_array = $_POST['tugas'] ?? []; // Associative array [id_tugas => id_pegawai]

            if (empty($tahun_ajaran) || empty($semester)) {
                throw new Exception("Filter Tahun Ajaran dan Semester tidak terdefinisi.");
            }

            $pdo->beginTransaction();
            try {
                // Hapus semua penugasan untuk filter ini, lalu insert ulang yang baru (Full Sync Strategy)
                $stmt_delete = $pdo->prepare("DELETE FROM penugasan_pegawai WHERE tahun_ajaran = ? AND semester = ?");
                $stmt_delete->execute([$tahun_ajaran, $semester]);

                $stmt_insert = $pdo->prepare("INSERT INTO penugasan_pegawai (id_pegawai, id_tugas, tahun_ajaran, semester) VALUES (?, ?, ?, ?)");

                $assigned_count = 0;
                foreach ($tugas_array as $id_tugas => $id_pegawai) {
                    if (!empty($id_pegawai)) {
                        $stmt_insert->execute([$id_pegawai, $id_tugas, $tahun_ajaran, $semester]);
                        $assigned_count++;
                    }
                }

                $pdo->commit();
                catat_log($pdo, 'Update Penugasan Pegawai', "Memperbarui $assigned_count penugasan untuk TA $tahun_ajaran $semester");
                $_SESSION['success_msg'] = "Berhasil menyimpan formasi sebanyak $assigned_count Penugasan Jabatan untuk $tahun_ajaran $semester.";

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            header("Location: index.php?ta=$tahun_ajaran&smt=$semester&tab=penugasan");
            exit;

        } elseif ($aksi === 'salin_penugasan') {
            $current_ta = trim($_POST['current_ta']);
            $current_smt = trim($_POST['current_smt']);

            // Simple logic: If current is Genap, copy from Ganjil same TA
            // If current is Ganjil, copy from Genap previous TA (Not implemented auto parsing here due to complex TA string formats, will just fetch the latest distinct TA/SMT prior to this)

            // Get all unique combinations ordered descending
            $stmt_combo = $pdo->query("SELECT DISTINCT tahun_ajaran, semester FROM penugasan_pegawai ORDER BY tahun_ajaran DESC, semester DESC");
            $combos = $stmt_combo->fetchAll(PDO::FETCH_ASSOC);

            $source_ta = null;
            $source_smt = null;

            $found_current = false;
            foreach ($combos as $c) {
                if ($c['tahun_ajaran'] == $current_ta && $c['semester'] == $current_smt) {
                    $found_current = true;
                    continue; // Skip the current one we want to paste INTO
                }

                // If we found current in previous iteration, OR if we never found current (meaning current is newly created), grab the first available as source
                if ($found_current || empty($combos)) {
                    $source_ta = $c['tahun_ajaran'];
                    $source_smt = $c['semester'];
                    break;
                }
            }

            // Fallback if not found sequentially (e.g., trying to copy into a brand new year)
            if (!$source_ta) {
                if (!empty($combos)) {
                    $source_ta = $combos[0]['tahun_ajaran'];
                    $source_smt = $combos[0]['semester'];
                } else {
                    throw new Exception("Tidak ada data riwayat penugasan dari semester/tahun sebelumnya yang bisa disalin.");
                }
            }

            $pdo->beginTransaction();
            try {
                // Get source assignments
                $stmt_get = $pdo->prepare("SELECT id_pegawai, id_tugas FROM penugasan_pegawai WHERE tahun_ajaran = ? AND semester = ?");
                $stmt_get->execute([$source_ta, $source_smt]);
                $sumber = $stmt_get->fetchAll();

                if (empty($sumber)) {
                    throw new Exception("Master Penugasan di TA $source_ta $source_smt ternyata kosong.");
                }

                // Delete current
                $stmt_delete = $pdo->prepare("DELETE FROM penugasan_pegawai WHERE tahun_ajaran = ? AND semester = ?");
                $stmt_delete->execute([$current_ta, $current_smt]);

                // Insert copied
                $stmt_insert = $pdo->prepare("INSERT INTO penugasan_pegawai (id_pegawai, id_tugas, tahun_ajaran, semester) VALUES (?, ?, ?, ?)");
                foreach ($sumber as $s) {
                    $stmt_insert->execute([$s['id_pegawai'], $s['id_tugas'], $current_ta, $current_smt]);
                }

                $pdo->commit();
                catat_log($pdo, 'Copy Penugasan Pegawai', "Menyalin " . count($sumber) . " penugasan dari $source_ta $source_smt ke $current_ta $current_smt");
                $_SESSION['success_msg'] = "Berhasil menyalin " . count($sumber) . " profil penugasan dari $source_ta $source_smt.";

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            header("Location: index.php?ta=$current_ta&smt=$current_smt&tab=penugasan");
            exit;

        } else {
            throw new Exception("Aksi tidak valid.");
        }

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Gagal memproses: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}
?>