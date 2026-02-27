<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin atau kepsek
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role_name']), ['administrator', 'kepala sekolah'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
        exit;
    }
    $_SESSION['error_msg'] = "Akses ditolak. Anda tidak memiliki izin untuk mengelola Master Kelas.";
    header("Location: " . base_url('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];

    try {
        if ($aksi === 'tambah') {
            $tahun_ajaran = trim($_POST['tahun_ajaran']);
            $tingkat = trim($_POST['tingkat']);
            $nama_kelas = trim($_POST['nama_kelas']);
            $id_wali_kelas_ganjil = empty($_POST['id_wali_kelas_ganjil']) ? null : (int) $_POST['id_wali_kelas_ganjil'];
            $id_wali_kelas_genap = empty($_POST['id_wali_kelas_genap']) ? null : (int) $_POST['id_wali_kelas_genap'];

            if (empty($tahun_ajaran) || empty($tingkat) || empty($nama_kelas)) {
                throw new Exception("Semua field bertanda * wajib diisi!");
            }

            $stmt = $pdo->prepare("INSERT INTO master_kelas (tahun_ajaran, tingkat, nama_kelas, id_wali_kelas_ganjil, id_wali_kelas_genap) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tahun_ajaran, $tingkat, $nama_kelas, $id_wali_kelas_ganjil, $id_wali_kelas_genap]);

            catat_log($pdo, 'Tambah Kelas Baru', "Menambahkan kelas $nama_kelas ($tingkat) untuk TA $tahun_ajaran");
            $_SESSION['success_msg'] = "Kelas $nama_kelas berhasil ditambahkan.";
            header("Location: index.php");
            exit;

        } elseif ($aksi === 'hapus') {
            $id_kelas = (int) $_POST['id_kelas'];

            // Cek apakah masih ada anggotanya
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM anggota_kelas WHERE id_kelas = ?");
            $stmt_cek->execute([$id_kelas]);
            if ($stmt_cek->fetchColumn() > 0) {
                throw new Exception("Kelas ini tidak dapat dihapus karena masih memiliki anggota siswa!");
            }

            // Ambil nama kelas untuk log
            $stmt_nama = $pdo->prepare("SELECT nama_kelas FROM master_kelas WHERE id_kelas = ?");
            $stmt_nama->execute([$id_kelas]);
            $nama_kelas = $stmt_nama->fetchColumn();

            $stmt_del = $pdo->prepare("DELETE FROM master_kelas WHERE id_kelas = ?");
            $stmt_del->execute([$id_kelas]);

            catat_log($pdo, 'Hapus Kelas', "Menghapus kelas kosong: $nama_kelas");
            $_SESSION['success_msg'] = "Kelas $nama_kelas berhasil dihapus.";
            header("Location: index.php");
            exit;

        } elseif ($aksi === 'pindah_siswa') {
            // AJAX Handler for Drag & Drop
            header('Content-Type: application/json');

            $id_siswa = (int) $_POST['id_siswa'];
            $id_kelas_tujuan = (int) $_POST['id_kelas_baru']; // Jika 0, berarti dikembalikan ke "Unassigned"

            $pdo->beginTransaction();

            try {
                // 1. Hapus siswa dari kelas manapun untuk Tahun Ajaran Ini
                // Pertama, cari tahu kelas-kelas yang ada di TA aktif
                $stmt_kelas_ta = $pdo->prepare("SELECT id_kelas FROM master_kelas WHERE tahun_ajaran = ?");
                $stmt_kelas_ta->execute([TAHUN_AJARAN]);
                $kelas_di_ta_ini = $stmt_kelas_ta->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($kelas_di_ta_ini)) {
                    $inQuery = implode(',', array_fill(0, count($kelas_di_ta_ini), '?'));
                    $params = $kelas_di_ta_ini;
                    array_unshift($params, $id_siswa); // Taruh di depan

                    $stmt_hapus_lama = $pdo->prepare("DELETE FROM anggota_kelas WHERE id_siswa = ? AND id_kelas IN ($inQuery)");
                    $stmt_hapus_lama->execute($params);
                }

                $nama_kelas_tujuan = 'Belum Ada Kelas';

                // 2. Jika tujuan bukan 0, masukkan ke kelas baru
                if ($id_kelas_tujuan > 0) {
                    $stmt_insert = $pdo->prepare("INSERT INTO anggota_kelas (id_kelas, id_siswa) VALUES (?, ?)");
                    $stmt_insert->execute([$id_kelas_tujuan, $id_siswa]);

                    // Ambil nama kelas
                    $stmt_nama_kls = $pdo->prepare("SELECT nama_kelas FROM master_kelas WHERE id_kelas = ?");
                    $stmt_nama_kls->execute([$id_kelas_tujuan]);
                    $nama_kelas_tujuan = $stmt_nama_kls->fetchColumn();
                }

                // 3. Update cache 'kelas' di tabel data_siswa
                $stmt_upd = $pdo->prepare("UPDATE data_siswa SET kelas = ? WHERE id_siswa = ?");
                $stmt_upd->execute([$id_kelas_tujuan > 0 ? $nama_kelas_tujuan : 'TIDAK ADA', $id_siswa]);

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Siswa berhasil dipindahkan.']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;

        } elseif ($aksi === 'luluskan_semua') {
            // Luluskan semua siswa pada jenjang tingkat XII (atau siswa yang dicentang, kita implementasi per kelas / tingkat)
            // Untuk lebih aman, kita luluskan berdasarkan id_kelas yang di-submit
            $id_kelas = (int) $_POST['id_kelas'];

            $stmt_nama = $pdo->prepare("SELECT nama_kelas FROM master_kelas WHERE id_kelas = ?");
            $stmt_nama->execute([$id_kelas]);
            $nama_kelas = $stmt_nama->fetchColumn();

            if (!$nama_kelas)
                throw new Exception("Kelas tidak ditemukan.");

            // Ambil semua siswa di kelas tersebut
            $stmt_siswa = $pdo->prepare("SELECT id_siswa FROM anggota_kelas WHERE id_kelas = ?");
            $stmt_siswa->execute([$id_kelas]);
            $list_siswa = $stmt_siswa->fetchAll(PDO::FETCH_COLUMN);

            if (empty($list_siswa)) {
                throw new Exception("Kelas ini kosong, tidak ada yang bisa diluluskan.");
            }

            $pdo->beginTransaction();
            try {
                // Update status_siswa menjadi Lulus dan tahun_lulus menjadi tahun saat ini (ex: 2025 dari 2024/2025)
                $explode_ta = explode('/', TAHUN_AJARAN);
                $tahun_lulus = count($explode_ta) > 1 ? $explode_ta[1] : TAHUN_AJARAN;

                $inQuery = implode(',', array_fill(0, count($list_siswa), '?'));
                $params = $list_siswa;

                // Tambahkan parameter Lulus dan Tahun lulus di akhir/awal tergantung susunan query
                $stmt_lulus = $pdo->prepare("UPDATE data_siswa SET status_siswa = 'Lulus', tahun_lulus = ? WHERE id_siswa IN ($inQuery)");
                array_unshift($params, $tahun_lulus); // Taruh param tahun_lulus di awal array params

                $stmt_lulus->execute($params);

                $pdo->commit();

                catat_log($pdo, 'Kelulusan Masal', "Meluluskan " . count($list_siswa) . " siswa dari kelas $nama_kelas (Tahun Lulus: $tahun_lulus)");
                $_SESSION['success_msg'] = count($list_siswa) . " Siswa kelas $nama_kelas berhasil dinyatakan lulus dengan Tahun Lulus $tahun_lulus.";

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            header("Location: index.php"); // Kembali ke index kelas
            exit;

        } elseif ($aksi === 'duplikasi_kelas') {
            $ta_sumber = trim($_POST['ta_sumber']);

            if (empty($ta_sumber)) {
                throw new Exception("Tahun Ajaran sumber harus dipilih!");
            }

            // Get classes from the source year
            $stmt_sumber = $pdo->prepare("SELECT * FROM master_kelas WHERE tahun_ajaran = ?");
            $stmt_sumber->execute([$ta_sumber]);
            $kelas_sumber = $stmt_sumber->fetchAll();

            if (empty($kelas_sumber)) {
                throw new Exception("Tahun Ajaran sumber tidak memiliki kelas untuk diduplikat.");
            }

            $pdo->beginTransaction();
            try {
                $jumlah_disalin = 0;
                $stmt_insert = $pdo->prepare("INSERT INTO master_kelas (tahun_ajaran, tingkat, nama_kelas, id_wali_kelas_ganjil, id_wali_kelas_genap) VALUES (?, ?, ?, ?, ?)");

                foreach ($kelas_sumber as $kelas_lama) {
                    $tingkat_lama = strtoupper(trim($kelas_lama['tingkat']));
                    $tingkat_baru = '';

                    // Logic Kenaikan Jenjang (contoh untuk SMA/SMK)
                    if ($tingkat_lama == 'X' || $tingkat_lama == '10') {
                        $tingkat_baru = is_numeric($tingkat_lama) ? '11' : 'XI';
                    } elseif ($tingkat_lama == 'XI' || $tingkat_lama == '11') {
                        $tingkat_baru = is_numeric($tingkat_lama) ? '12' : 'XII';
                    } elseif ($tingkat_lama == 'XII' || $tingkat_lama == '12') {
                        // Jangan salin kelas XII karena sudah lulus
                        continue;
                    } else {
                        // Jika format lain (SMP 7,8,9 atau SD 1-6), sesuaikan logikanya
                        // Misalnya jika numeric: $tingkat_baru = (int)$tingkat_lama + 1;
                        if (is_numeric($tingkat_lama)) {
                            $tingkat_baru = (string) ((int) $tingkat_lama + 1);
                        } else {
                            $tingkat_baru = $tingkat_lama; // Tetap jika tidak terbaca
                        }
                    }

                    // Ganti teks tingkat di dalam nama kelas (misal: "X TKJ 1" -> "XI TKJ 1")
                    // Menggunakan regex untuk mencari kata utuh (word boundary) yang sama dengan tingkat lama
                    $nama_kelas_baru = preg_replace('/\b' . preg_quote($tingkat_lama, '/') . '\b/i', $tingkat_baru, $kelas_lama['nama_kelas'], 1);

                    // Hanya salin id_wali, tidak menyalin anggota kelas. Anggota kelas dikelola terpisah via Pindah Kelas (Drag&Drop)
                    // Siswa diurus di halaman `manage.php`. Di sini murni struktur kelas saja.
                    if (!empty($tingkat_baru)) {
                        $stmt_insert->execute([
                            TAHUN_AJARAN, // Tujuan adalah TA saat ini
                            $tingkat_baru,
                            $nama_kelas_baru,
                            $kelas_lama['id_wali_kelas_ganjil'],
                            $kelas_lama['id_wali_kelas_genap']
                        ]);
                        $jumlah_disalin++;
                    }
                }

                $pdo->commit();
                catat_log($pdo, 'Duplikat Struktur Kelas', "Menduplikat $jumlah_disalin struktur kelas dari TA $ta_sumber ke TA " . TAHUN_AJARAN);
                $_SESSION['success_msg'] = "Berhasil menduplikat $jumlah_disalin struktur kelas dari T.A. $ta_sumber.";
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            header("Location: index.php");
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
