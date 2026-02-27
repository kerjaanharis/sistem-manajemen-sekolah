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

// Cek ID Siswa
$id_siswa = $_GET['id'] ?? null;
if (!$id_siswa) {
    $_SESSION['error_msg'] = "ID Siswa tidak ditemukan.";
    header("Location: " . base_url('modules/siswa/index.php'));
    exit;
}

// Ambil Data Siswa
try {
    $stmt = $pdo->prepare("SELECT s.*, u.username 
                           FROM data_siswa s 
                           LEFT JOIN users u ON s.id_user = u.id_user 
                           WHERE s.id_siswa = ?");
    $stmt->execute([$id_siswa]);
    $siswa = $stmt->fetch();

    if (!$siswa) {
        $_SESSION['error_msg'] = "Data Siswa tidak ditemukan di database.";
        header("Location: " . base_url('modules/siswa/index.php'));
        exit;
    }
} catch (\PDOException $e) {
    $_SESSION['error_msg'] = "Gagal mengambil data siswa: " . $e->getMessage();
    header("Location: " . base_url('modules/siswa/index.php'));
    exit;
}

// Auto-add Address Columns if not exist
try {
    $columns_to_add = [
        "ADD COLUMN `alamat_lengkap` TEXT DEFAULT NULL",
        "ADD COLUMN `rt` VARCHAR(5) DEFAULT NULL",
        "ADD COLUMN `rw` VARCHAR(5) DEFAULT NULL",
        "ADD COLUMN `dusun` VARCHAR(100) DEFAULT NULL",
        "ADD COLUMN `desa_kelurahan` VARCHAR(100) DEFAULT NULL",
        "ADD COLUMN `kecamatan` VARCHAR(100) DEFAULT NULL",
        "ADD COLUMN `kota_kabupaten` VARCHAR(100) DEFAULT NULL",
        "ADD COLUMN `provinsi` VARCHAR(100) DEFAULT NULL",
        "ADD COLUMN `kode_pos` VARCHAR(10) DEFAULT NULL",
        "ADD COLUMN `lintang` VARCHAR(50) DEFAULT NULL",
        "ADD COLUMN `bujur` VARCHAR(50) DEFAULT NULL"
    ];

    foreach ($columns_to_add as $col_def) {
        try {
            $pdo->exec("ALTER TABLE `data_siswa` " . $col_def);
        } catch (PDOException $e) {
            // Column likely already exists, ignore safely
        }
    }
} catch (Exception $e) {
    // General catch
}


// Auto-create & Fetch Data Bantuan
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `siswa_bantuan` (
        `id_bantuan` int(11) NOT NULL AUTO_INCREMENT,
        `id_siswa` int(11) NOT NULL,
        `jenis_bantuan` enum('Program Indonesia Pintar (PIP)','Gerakan Nasional Orang Tua Asuh (GNOTA)','Program Keluarga Harapan (PKH)','Beasiswa Dari Pemerintah','Beasiswa Dari Sekolah','Program Bantuan Lain') NOT NULL,
        `tahun_diterima` varchar(10) DEFAULT NULL,
        `periode` varchar(20) DEFAULT NULL,
        `tanggal_penerimaan` date DEFAULT NULL,
        `nominal` bigint(20) DEFAULT 0,
        `keterangan` text DEFAULT NULL,
        `created_at` timestamp DEFAULT current_timestamp(),
        PRIMARY KEY (`id_bantuan`),
        KEY `fk_bantuan_siswa` (`id_siswa`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Add columns if they don't exist yet (in case table was created by previous version)
    try {
        $pdo->exec("ALTER TABLE `siswa_bantuan` ADD COLUMN `periode` varchar(20) DEFAULT NULL AFTER `tahun_diterima`");
    } catch (PDOException $e) {
    }
    try {
        $pdo->exec("ALTER TABLE `siswa_bantuan` ADD COLUMN `tanggal_penerimaan` date DEFAULT NULL AFTER `periode`");
    } catch (PDOException $e) {
    }
    try {
        $pdo->exec("ALTER TABLE `siswa_bantuan` ADD COLUMN `nominal` bigint(20) DEFAULT 0 AFTER `tanggal_penerimaan`");
    } catch (PDOException $e) {
    }

    $stmt_bantuan = $pdo->prepare("SELECT * FROM siswa_bantuan WHERE id_siswa = ? ORDER BY tahun_diterima DESC, created_at DESC");
    $stmt_bantuan->execute([$id_siswa]);
    $bantuan_list = $stmt_bantuan->fetchAll();
} catch (\PDOException $e) {
    $bantuan_list = [];
}

$page_title = 'Detail Siswa: ' . $siswa['nama_lengkap'];
$current_page = 'siswa';

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<!-- Navigation Breadcrumb -->
<nav class="flex text-sm font-medium text-slate-500 mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-2">
        <li class="inline-flex items-center">
            <a href="<?= base_url('modules/siswa/index.php') ?>" class="inline-flex items-center hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-1.5 text-sm"></i> Kembali ke Daftar Siswa
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-slate-300 text-[10px] mx-2"></i>
                <span class="text-slate-600">Detail Profil Siswa</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header Profile -->
<div
    class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 flex flex-col sm:flex-row items-center sm:items-start gap-6 relative overflow-hidden">
    <!-- Dekorasi -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 -mr-20 -mt-20"></div>

    <img src="https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama_lengkap']) ?>&background=random&color=fff&rounded=true&size=128"
        alt="Avatar" class="h-24 w-24 rounded-full border-4 border-white shadow-md bg-slate-100 shrink-0 mt-2">
    <div class="flex-1 w-full text-center sm:text-left z-10 flex flex-col justify-start">
        
        <!-- Name -->
        <h3 class="text-2xl font-bold text-slate-800 lg:mt-0 mb-2">
            <?= htmlspecialchars($siswa['nama_lengkap']) ?>
        </h3>

        <!-- Badges -->
        <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-4">
            <?php if ($siswa['status_siswa'] == 'Aktif'): ?>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fas fa-circle text-[8px] mr-1.5 text-emerald-500"></i> AKTIF
                </span>
            <?php else: ?>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fas fa-circle text-[8px] mr-1.5 text-slate-400"></i>
                    <?= strtoupper($siswa['status_siswa']) ?>
                </span>
            <?php endif; ?>
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                <i class="fas fa-chalkboard-user mr-1.5"></i> Kelas
                <?= htmlspecialchars($siswa['kelas']) ?>
            </span>
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200">
                <i class="fas fa-id-badge mr-1.5"></i> NIS:
                <?= htmlspecialchars($siswa['nis']) ?>
            </span>
        </div>

    </div>
</div>

<!-- Main Tabs Section -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">

    <!-- Tab Navigation -->
    <div class="border-b border-slate-100 bg-slate-50/50 px-2 sm:px-4 overflow-x-auto scrollbar-hide">
        <nav class="-mb-px flex space-x-2 sm:space-x-4 min-w-max" aria-label="Tabs">
            <!-- Tabs Item -->
            <?php
            $tabs = [
                'biodata' => ['ikon' => 'fa-id-card', 'label' => '1. Biodata Siswa'],
                'alamat' => ['ikon' => 'fa-map-location-dot', 'label' => '2. Alamat Siswa'],
                'ortu' => ['ikon' => 'fa-users', 'label' => '3. Data Orang Tua'],
                'pembayaran' => ['ikon' => 'fa-file-invoice-dollar', 'label' => '4. Catatan Pembayaran'],
                'prestasi' => ['ikon' => 'fa-medal', 'label' => '5. Catatan Prestasi'],
                'pelanggaran' => ['ikon' => 'fa-triangle-exclamation', 'label' => '6. Catatan Pelanggaran'],
                'lomba' => ['ikon' => 'fa-trophy', 'label' => '7. Keikutsertaan Lomba'],
                'bantuan' => ['ikon' => 'fa-hand-holding-heart', 'label' => '8. Penerima Bantuan'],
                'log' => ['ikon' => 'fa-clock-rotate-left', 'label' => '9. Log Siswa'],
            ];

            $first = true;
            foreach ($tabs as $id => $tab):
                $activeClass = $first ? 'border-primary text-primary bg-blue-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300';
                ?>
                <button onclick="switchTab('<?= $id ?>', this)"
                    class="tab-btn <?= $activeClass ?> whitespace-nowrap py-4 px-3 sm:px-4 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <i
                        class="fas <?= $tab['ikon'] ?> mr-2 <?= $first ? 'text-primary' : 'text-slate-400' ?> tab-icon transition-colors"></i>
                    <?= $tab['label'] ?>
                </button>
                <?php $first = false; endforeach; ?>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="p-6">

        <!-- TAB: Biodata Siswa -->
        <div id="tab-biodata" class="tab-content block animate-fadeIn">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 border-b border-slate-100 pb-2 gap-3">
                <h4 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i> Informasi Dasar (Biodata)
                </h4>
                <a href="edit.php?id=<?= $id_siswa ?>" class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-md hover:bg-blue-100 transition-colors flex items-center shrink-0 w-fit">
                    <i class="fas fa-edit mr-1.5"></i> Edit Data Biodata
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 text-sm">
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama
                        Lengkap</span>
                    <span class="text-slate-800 font-semibold text-base">
                        <?= htmlspecialchars($siswa['nama_lengkap']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis
                        Kelamin</span>
                    <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                        <span class="text-slate-800 font-medium"><i
                                class="fas fa-mars text-blue-500 mr-2"></i>Laki-laki</span>
                    <?php else: ?>
                        <span class="text-slate-800 font-medium"><i
                                class="fas fa-venus text-pink-500 mr-2"></i>Perempuan</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Induk
                        Siswa (NIS)</span>
                    <span
                        class="text-slate-800 font-semibold font-mono bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                        <?= htmlspecialchars($siswa['nis']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Induk
                        Siswa Nasional (NISN)</span>
                    <span class="text-slate-800 font-semibold font-mono <?= !$siswa['nisn'] ? 'text-slate-400' : '' ?>">
                        <?= htmlspecialchars($siswa['nisn'] ?: 'Belum Ada') ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kelas Saat
                        Ini</span>
                    <span class="text-slate-800 font-medium">
                        <?= htmlspecialchars($siswa['kelas']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahun
                        Angkatan</span>
                    <span class="text-slate-800 font-medium">
                        <?= htmlspecialchars($siswa['angkatan'] ?? 'Belum Diatur') ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status
                        Kesiswaan</span>
                    <span class="text-slate-800 font-medium">
                        <?= htmlspecialchars($siswa['status_siswa']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahun
                        Lulus</span>
                    <span class="text-slate-800 font-medium <?= !$siswa['tahun_lulus'] ? 'text-slate-400' : '' ?>">
                        <?= htmlspecialchars($siswa['tahun_lulus'] ?: 'Belum Lulus') ?>
                    </span>
                </div>
                <div class="md:col-span-2">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Akun SSO
                        (Single Sign-On) Terkait</span>
                    <?php if ($siswa['username']): ?>
                        <span
                            class="text-indigo-700 font-medium font-mono bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100 flex items-center w-max">
                            <i class="fas fa-link mr-2 text-indigo-400"></i>
                            <?= htmlspecialchars($siswa['username']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-slate-500 font-medium flex items-center italic">
                            <i class="fas fa-link-slash mr-2 text-slate-400"></i> Belum terhubung dengan akun login.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: Alamat & Kontak Siswa -->
        <div id="tab-alamat" class="tab-content hidden animate-fadeIn">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 border-b border-slate-100 pb-2 gap-3">
                <h4 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-map-location-dot text-primary mr-2"></i> Alamat & Tempat Tinggal Siswa
                </h4>
                <button type="button" onclick="toggleEdit('alamat')" id="btn-edit-alamat" class="text-sm font-medium text-amber-600 bg-amber-50 px-3 py-1.5 rounded-md hover:bg-amber-100 transition-colors flex items-center shrink-0 w-fit">
                    <i class="fas fa-edit mr-1.5"></i> Edit Data Alamat
                </button>
            </div>
            
            <form action="proses_alamat.php" method="POST" class="space-y-6" id="form-alamat">
                <fieldset disabled id="fs-alamat" class="group disabled:opacity-85 transition-opacity">
                <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kolom Kiri: Input Alamat -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" rows="3" placeholder="Nama Jalan, Gedung, Perumahan..." class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"><?= htmlspecialchars($siswa['alamat_lengkap'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">RT</label>
                                <input type="text" name="rt" value="<?= htmlspecialchars($siswa['rt'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">RW</label>
                                <input type="text" name="rw" value="<?= htmlspecialchars($siswa['rw'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dusun / Lingkungan</label>
                            <input type="text" name="dusun" value="<?= htmlspecialchars($siswa['dusun'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Desa / Kelurahan</label>
                                <input type="text" name="desa_kelurahan" value="<?= htmlspecialchars($siswa['desa_kelurahan'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan</label>
                                <input type="text" name="kecamatan" value="<?= htmlspecialchars($siswa['kecamatan'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kota / Kabupaten</label>
                                <input type="text" name="kota_kabupaten" value="<?= htmlspecialchars($siswa['kota_kabupaten'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Provinsi</label>
                                <input type="text" name="provinsi" value="<?= htmlspecialchars($siswa['provinsi'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kode Pos</label>
                            <input type="text" name="kode_pos" value="<?= htmlspecialchars($siswa['kode_pos'] ?? '') ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-ful p-2.5 w-1/2">
                        </div>
                    </div>

                    <!-- Kolom Kanan: Peta Leaflet & Koordinat -->
                    <div class="space-y-4">
                        <div class="bg-slate-50 p-4 border border-slate-200 rounded-xl">
                            <h5 class="text-sm font-bold text-slate-800 mb-3 flex items-center">
                                <i class="fas fa-location-crosshairs text-blue-500 mr-2"></i> Titik Koordinat Rumah Siswa
                            </h5>
                            
                            <!-- Leaflet CSS & JS -->
                            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
                            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

                            <div id="leafletMap" style="min-height: 256px; width: 100%; position: relative; z-index: 1;" class="rounded-lg border border-slate-300 mb-4 z-0"></div>
                            
                            <!-- Fix CSS Conflicts between Leaflet & Tailwind -->
                            <style>
                                .leaflet-container { z-index: 1 !important; }
                                .leaflet-pane { z-index: 1 !important; }
                                .leaflet-top, .leaflet-bottom { z-index: 2 !important; }
                            </style>
                            
                            <p class="text-[11px] text-slate-500 mb-4"><i class="fas fa-info-circle mr-1"></i> Geser pin merah (marker) untuk menyesuaikan titik lokasi rumah secara akurat.</p>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Garis Lintang (Latitude)</label>
                                    <input type="text" name="lintang" id="inputLintang" value="<?= htmlspecialchars($siswa['lintang'] ?? '') ?>" onchange="updateMarkerFromInput()" class="bg-slate-50 border text-slate-800 border-slate-200 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2 font-mono">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Garis Bujur (Longitude)</label>
                                    <input type="text" name="bujur" id="inputBujur" value="<?= htmlspecialchars($siswa['bujur'] ?? '') ?>" onchange="updateMarkerFromInput()" class="bg-slate-50 border text-slate-800 border-slate-200 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2 font-mono">
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4 flex items-center justify-end space-x-3 hidden" id="action-alamat">
                            <button type="button" onclick="cancelEdit('alamat')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center text-sm">
                                <i class="fas fa-save mr-2"></i> Simpan Alamat
                            </button>
                        </div>
                    </div>
                </div>
                </fieldset>
                    </div>
                </div>
            </form>
        </div>
        
        <script>
            // Initialization script for Leaflet when tab is opened
            let mapInitialized = false;
            let theMap, marker;
            
            function initLeafletMap() {
                if(mapInitialized) return;
                
                // Get pre-filled coordinates or default to central Indonesia (approx)
                let lat = document.getElementById('inputLintang').value || -0.789275;
                let lng = document.getElementById('inputBujur').value || 113.921327;
                let zoomLvl = document.getElementById('inputLintang').value ? 16 : 5;

                theMap = L.map('leafletMap').setView([lat, lng], zoomLvl);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(theMap);

                marker = L.marker([lat, lng], {draggable: true}).addTo(theMap);

                marker.on('dragend', function(e) {
                    var position = marker.getLatLng();
                    document.getElementById('inputLintang').value = position.lat.toFixed(6);
                    document.getElementById('inputBujur').value = position.lng.toFixed(6);
                });
                
                theMap.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    document.getElementById('inputLintang').value = e.latlng.lat.toFixed(6);
                    document.getElementById('inputBujur').value = e.latlng.lng.toFixed(6);
                });

                mapInitialized = true;
            }

            function updateMarkerFromInput() {
                if(!theMap || !marker) return;
                let lat = parseFloat(document.getElementById('inputLintang').value);
                let lng = parseFloat(document.getElementById('inputBujur').value);
                
                if(!isNaN(lat) && !isNaN(lng)) {
                    let newLatLng = new L.LatLng(lat, lng);
                    marker.setLatLng(newLatLng);
                    theMap.setView(newLatLng, theMap.getZoom());
                }
            }

            // Using MutationObserver to detect when the tab becomes visible
            const alamatTab = document.getElementById('tab-alamat');
            if (alamatTab) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === "class") {
                            const classList = mutation.target.classList;
                            if (!classList.contains('hidden')) {
                                // Tab is now visible
                                setTimeout(() => {
                                    initLeafletMap();
                                    if(theMap) {
                                        theMap.invalidateSize();
                                    }
                                }, 100);
                            }
                        }
                    });
                });
                
                observer.observe(alamatTab, { attributes: true });
            }
        </script>

        <!-- TAB: Data Orang Tua -->
        <div id="tab-ortu" class="tab-content hidden animate-fadeIn">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 border-b border-slate-100 pb-2 gap-3">
                <h4 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-users text-primary mr-2"></i> Data Orang Tua / Wali Lengkap
                </h4>
                <button type="button" onclick="toggleEdit('ortu')" id="btn-edit-ortu" class="text-sm font-medium text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-md hover:bg-emerald-100 transition-colors flex items-center shrink-0 w-fit">
                    <i class="fas fa-edit mr-1.5"></i> Edit Data Orang Tua
                </button>
            </div>
            
            <form action="proses_orang_tua.php" method="POST" class="space-y-8" id="form-ortu">
                <fieldset disabled id="fs-ortu" class="group disabled:opacity-85 transition-opacity">
                <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">

                <?php
                $ortu_sections = [
                    'ayah' => ['title' => 'A. DATA AYAH', 'icon' => 'fa-user-tie', 'color' => 'blue', 'has_status' => true],
                    'ibu' => ['title' => 'B. DATA IBU', 'icon' => 'fa-person-dress', 'color' => 'pink', 'has_status' => true],
                    'wali' => ['title' => 'C. DATA WALI', 'icon' => 'fa-user-shield', 'color' => 'emerald', 'has_status' => false]
                ];

                $opt_pendidikan = ['Tidak Bersekolah', 'SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'];
                $opt_pekerjaan = ['Tidak Bekerja', 'Pensiunan', 'PNS', 'TNI/Polisi', 'Guru/Dosen', 'Pegawai Swasta', 'Wiraswasta', 'Pengacara/Jaksa/Hakim/Notaris', 'Seniman/Pelukis/Artis/Sejenis', 'Dokter/Bidan/Perawat', 'Pilot/Pramugara', 'Pedagang', 'Petani/Peternak', 'Nelayan', 'Buruh (Tani/Pabrik/Bangunan)', 'Sopir/Masinis/Kondektur', 'Politikus', 'Lainnya'];
                $opt_penghasilan = ['Tidak ada', 'Kurang dari 500.000', '500.000 - 1.000.000', '1.000.001 - 2.000.000', '2.000.001 - 3.000.000', '3.000.001 - 5.000.000', 'Lebih dari 5.000.000'];

                foreach ($ortu_sections as $type => $sec): 
                    $color = $sec['color'];
                ?>
                <div class="bg-<?= $color ?>-50/30 border border-<?= $color ?>-100 rounded-xl p-5">
                    <h5 class="font-bold text-<?= $color ?>-800 mb-4 flex items-center border-b border-<?= $color ?>-100 pb-2">
                        <i class="fas <?= $sec['icon'] ?> mr-2"></i> <?= $sec['title'] ?>
                    </h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if($sec['has_status']): ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status <?= ucfirst($type) ?></label>
                            <select name="status_<?= $type ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="Hidup" <?= ($siswa["status_$type"] ?? 'Hidup') == 'Hidup' ? 'selected' : '' ?>>Hidup</option>
                                <option value="Meninggal" <?= ($siswa["status_$type"] ?? '') == 'Meninggal' ? 'selected' : '' ?>>Meninggal</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NIK <?= ucfirst($type) ?></label>
                            <input type="text" name="nik_<?= $type ?>" value="<?= htmlspecialchars($siswa["nik_$type"] ?? '') ?>" pattern="\d{16}" maxlength="16" placeholder="16 Digit Angka" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <?= ucfirst($type) ?></label>
                            <input type="text" name="nama_<?= $type ?>" value="<?= htmlspecialchars($siswa["nama_$type"] ?? '') ?>" placeholder="Nama Lengkap" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Lahir</label>
                            <input type="number" name="tahun_lahir_<?= $type ?>" value="<?= htmlspecialchars($siswa["tahun_lahir_$type"] ?? '') ?>" min="1900" max="<?= date('Y') ?>" placeholder="YYYY" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pendidikan <?= ucfirst($type) ?></label>
                            <select name="pendidikan_<?= $type ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="">-- Pilih --</option>
                                <?php foreach($opt_pendidikan as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa["pendidikan_$type"] ?? '') == $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pekerjaan <?= ucfirst($type) ?></label>
                            <select name="pekerjaan_<?= $type ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="">-- Pilih --</option>
                                <?php foreach($opt_pekerjaan as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa["pekerjaan_$type"] ?? '') == $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Penghasilan <?= ucfirst($type) ?></label>
                            <select name="penghasilan_<?= $type ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="">-- Pilih --</option>
                                <?php foreach($opt_penghasilan as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa["penghasilan_$type"] ?? '') == $p ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">No HP <?= ucfirst($type) ?></label>
                            <input type="text" name="no_hp_<?= $type ?>" value="<?= htmlspecialchars($siswa["no_hp_$type"] ?? '') ?>" pattern="^08[0-9]{6,12}$" maxlength="14" placeholder="Cth: 08123456789" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>

                        <?php if($type === 'wali'): ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Hubungan dengan Siswa</label>
                            <select name="hubungan_wali" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="">-- Pilih Hubungan --</option>
                                <?php
                                $opt_hubungan = ['Kakek', 'Nenek', 'Paman', 'Bibi', 'Kakak', 'Keluarga Lain', 'Orang Tua Asuh', 'Lainnya'];
                                foreach ($opt_hubungan as $h):
                                ?>
                                    <option value="<?= $h ?>" <?= ($siswa["hubungan_wali"] ?? '') == $h ? 'selected' : '' ?>><?= $h ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Lengkap <?= ucfirst($type) ?></label>
                        <textarea name="alamat_<?= $type ?>" rows="2" placeholder="Alamat jalan, RT/RW, dsb..." class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"><?= htmlspecialchars($siswa["alamat_$type"] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="flex justify-end pt-4 border-t border-slate-100 hidden" id="action-ortu">
                    <button type="button" onclick="cancelEdit('ortu')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors mr-3 text-sm">
                        Batal
                    </button>
                    <button type="submit" class="text-white bg-primary hover:bg-primary/90 focus:ring-4 focus:ring-primary/30 font-medium rounded-lg text-sm px-6 py-2.5 text-center transition-colors flex items-center shadow-sm">
                        <i class="fas fa-save mr-2"></i> Simpan Data Orang Tua
                    </button>
                </div>
                </fieldset>
            </form>
        </div>

        <!-- 5 Placeholder Tabs -->
        <?php
        $placeholders = [
            'pembayaran' => ['icon' => 'fa-file-invoice-dollar', 'title' => 'Catatan Riwayat Pembayaran'],
            'prestasi' => ['icon' => 'fa-medal', 'title' => 'Catatan Prestasi Akademik & Non-Akademik'],
            'pelanggaran' => ['icon' => 'fa-triangle-exclamation', 'title' => 'Catatan Pelanggaran Kedisiplinan'],
            'lomba' => ['icon' => 'fa-trophy', 'title' => 'Riwayat Keikutsertaan Lomba'],
            'log' => ['icon' => 'fa-clock-rotate-left', 'title' => 'Log Aktivitas Siswa Secara Mandiri'],
        ];

        foreach ($placeholders as $id => $ph):
            ?>
                <div id="tab-<?= $id ?>" class="tab-content hidden animate-fadeIn">
                    <h4 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2 flex items-center">
                        <i class="fas <?= $ph['icon'] ?> text-primary mr-2"></i>
                        <?= $ph['title'] ?>
                    </h4>

                    <!-- Empty State / Placeholder -->
                    <div class="text-center py-16 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <div
                            class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-slate-100 text-slate-300">
                            <i class="fas <?= $ph['icon'] ?> text-3xl"></i>
                        </div>
                        <h5 class="text-slate-700 font-bold mb-1">Modul Dalam Pengembangan</h5>
                        <p class="text-sm text-slate-500 max-w-md mx-auto">
                            Data untuk <b>
                                <?= $ph['title'] ?>
                            </b> masih dalam tahap persiapan dan pengembangan struktur database. Fitur ini akan segera hadir.
                        </p>
                    </div>
                </div>
        <?php endforeach; ?>

        <!-- TAB: Penerima Bantuan -->
        <div id="tab-bantuan" class="tab-content hidden animate-fadeIn">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h4 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-hand-holding-heart text-primary mr-2"></i> Catatan Penerima Bantuan
                </h4>
                <button onclick="document.getElementById('modal-tambah-bantuan').classList.remove('hidden')"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm flex items-center">
                    <i class="fas fa-plus mr-1.5"></i> Tambah Data
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-600 uppercase bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold text-center w-12">No</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tgl Terima</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Jenis Bantuan</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tahun/Periode</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-right max-w-[150px]">Nominal (Rp)</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bantuan_list)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                        <i class="fas fa-folder-open text-2xl mb-2 opacity-50 block"></i>
                                        Belum ada catatan program bantuan untuk siswa ini.
                                    </td>
                                </tr>
                        <?php else: ?>
                                <?php $no = 1;
                                foreach ($bantuan_list as $b): ?>
                                        <tr class="bg-white border-b border-slate-50 hover:bg-slate-50/70 transition-colors">
                                            <td class="px-4 py-3 text-center font-medium text-slate-500"><?= $no++ ?></td>
                                            <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                                <?= $b['tanggal_penerimaan'] ? date('d M Y', strtotime($b['tanggal_penerimaan'])) : '-' ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-bold uppercase tracking-wider rounded border border-blue-100 whitespace-nowrap">
                                                    <?= htmlspecialchars($b['jenis_bantuan']) ?>
                                                </span>
                                                <?php if ($b['keterangan']): ?>
                                                        <p class="text-[10px] text-slate-400 mt-1 italic truncate max-w-[200px]"
                                                            title="<?= htmlspecialchars($b['keterangan']) ?>">
                                                            <?= htmlspecialchars($b['keterangan']) ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600 text-sm">
                                                <span
                                                    class="font-medium"><?= htmlspecialchars($b['tahun_diterima'] ?: '-') ?></span><br>
                                                <span
                                                    class="text-xs text-slate-400"><?= htmlspecialchars($b['periode'] ?: '-') ?></span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-emerald-600 font-mono">
                                                <?= number_format($b['nominal'], 0, ',', '.') ?>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center space-x-1">
                                                    <button type="button" onclick='editBantuan(<?= json_encode([
                                                        "id_bantuan" => $b["id_bantuan"],
                                                        "jenis_bantuan" => $b["jenis_bantuan"],
                                                        "tahun_diterima" => $b["tahun_diterima"],
                                                        "periode" => $b["periode"],
                                                        "tanggal_penerimaan" => $b["tanggal_penerimaan"],
                                                        "nominal" => $b["nominal"],
                                                        "keterangan" => $b["keterangan"]
                                                    ]) ?>)'
                                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors border border-amber-200"
                                                        title="Edit Catatan">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </button>
                                                    <form action="proses_bantuan.php" method="POST" class="inline m-0">
                                                        <input type="hidden" name="aksi" value="hapus_bantuan">
                                                        <input type="hidden" name="id_bantuan" value="<?= $b['id_bantuan'] ?>">
                                                        <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">
                                                        <button type="button"
                                                            onclick="confirmAksi(event, this.form, 'Hapus Catatan?', 'Hapus catatan <?= htmlspecialchars($b['jenis_bantuan']) ?> ini?', 'Hapus', '#dc2626')"
                                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors border border-red-200"
                                                            title="Hapus Catatan">
                                                            <i class="fas fa-trash-can text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Tambah Bantuan -->
<div id="modal-tambah-bantuan" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
        onclick="document.getElementById('modal-tambah-bantuan').classList.add('hidden')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                <form action="proses_bantuan.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah_bantuan">
                    <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                <i class="fas fa-hand-holding-heart text-primary mr-2"></i> Tambah Bantuan
                            </h3>
                            <button type="button"
                                onclick="document.getElementById('modal-tambah-bantuan').classList.add('hidden')"
                                class="text-slate-400 hover:text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg p-1.5 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Program Bantuan <span
                                        class="text-red-500">*</span></label>
                                <select name="jenis_bantuan" required
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                    <option value="">-- Pilih Jenis Bantuan --</option>
                                    <option value="Program Indonesia Pintar (PIP)">1. Program Indonesia Pintar (PIP)
                                    </option>
                                    <option value="Gerakan Nasional Orang Tua Asuh (GNOTA)">2. Gerakan Nasional Orang
                                        Tua Asuh (GNOTA)</option>
                                    <option value="Program Keluarga Harapan (PKH)">3. Program Keluarga Harapan (PKH)
                                    </option>
                                    <option value="Beasiswa Dari Pemerintah">4. Beasiswa Dari Pemerintah</option>
                                    <option value="Beasiswa Dari Sekolah">5. Beasiswa Dari Sekolah</option>
                                    <option value="Program Bantuan Lain">6. Program Bantuan Lain</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tahun
                                        Penerimaan</label>
                                    <input type="number" name="tahun_diterima" value="<?= date('Y') ?>" min="2000"
                                        max="2100"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Periode</label>
                                    <select name="periode"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                        <option value="">-- Pilih --</option>
                                        <option value="Periode 1">Periode 1</option>
                                        <option value="Periode 2">Periode 2</option>
                                        <option value="Periode 3">Periode 3</option>
                                        <option value="Periode 4">Periode 4</option>
                                        <option value="Periode 5">Periode 5</option>
                                        <option value="Ganjil">Semester Ganjil</option>
                                        <option value="Genap">Semester Genap</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal
                                        Penerimaan</label>
                                    <input type="date" name="tanggal_penerimaan"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nominal
                                        Diterima</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                            <span class="text-slate-500 font-medium sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="nominal" min="0" placeholder="1000000"
                                            class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full ps-10 p-2.5">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Tambahan
                                    (Opsional)</label>
                                <textarea name="keterangan" rows="2"
                                    placeholder="Contoh: Bantuan dicairkan lewat Bank BRI..."
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto transition-colors">
                            <i class="fas fa-save mr-2 mt-0.5"></i> Simpan Data
                        </button>
                        <button type="button"
                            onclick="document.getElementById('modal-tambah-bantuan').classList.add('hidden')"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Bantuan -->
<div id="modal-edit-bantuan" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
        onclick="document.getElementById('modal-edit-bantuan').classList.add('hidden')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                <form action="proses_bantuan.php" method="POST">
                    <input type="hidden" name="aksi" value="edit_bantuan">
                    <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">
                    <input type="hidden" name="id_bantuan" id="edit_id_bantuan" value="">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                <i class="fas fa-pen-to-square text-amber-500 mr-2"></i> Edit Bantuan
                            </h3>
                            <button type="button"
                                onclick="document.getElementById('modal-edit-bantuan').classList.add('hidden')"
                                class="text-slate-400 hover:text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg p-1.5 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Program Bantuan <span
                                        class="text-red-500">*</span></label>
                                <select name="jenis_bantuan" id="edit_jenis_bantuan" required
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                    <option value="">-- Pilih Jenis Bantuan --</option>
                                    <option value="Program Indonesia Pintar (PIP)">1. Program Indonesia Pintar (PIP)
                                    </option>
                                    <option value="Gerakan Nasional Orang Tua Asuh (GNOTA)">2. Gerakan Nasional Orang
                                        Tua Asuh (GNOTA)</option>
                                    <option value="Program Keluarga Harapan (PKH)">3. Program Keluarga Harapan (PKH)
                                    </option>
                                    <option value="Beasiswa Dari Pemerintah">4. Beasiswa Dari Pemerintah</option>
                                    <option value="Beasiswa Dari Sekolah">5. Beasiswa Dari Sekolah</option>
                                    <option value="Program Bantuan Lain">6. Program Bantuan Lain</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tahun
                                        Penerimaan</label>
                                    <input type="number" name="tahun_diterima" id="edit_tahun_diterima" min="2000"
                                        max="2100"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Periode</label>
                                    <select name="periode" id="edit_periode"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                        <option value="">-- Pilih --</option>
                                        <option value="Periode 1">Periode 1</option>
                                        <option value="Periode 2">Periode 2</option>
                                        <option value="Periode 3">Periode 3</option>
                                        <option value="Periode 4">Periode 4</option>
                                        <option value="Periode 5">Periode 5</option>
                                        <option value="Ganjil">Semester Ganjil</option>
                                        <option value="Genap">Semester Genap</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal
                                        Penerimaan</label>
                                    <input type="date" name="tanggal_penerimaan" id="edit_tanggal_penerimaan"
                                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nominal
                                        Diterima</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                            <span class="text-slate-500 font-medium sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="nominal" id="edit_nominal" min="0"
                                            class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full ps-10 p-2.5">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Tambahan
                                    (Opsional)</label>
                                <textarea name="keterangan" id="edit_keterangan" rows="2"
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition-colors">
                            <i class="fas fa-save mr-2 mt-0.5"></i> Simpan Perubahan
                        </button>
                        <button type="button"
                            onclick="document.getElementById('modal-edit-bantuan').classList.add('hidden')"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editBantuan(data) {
        document.getElementById('edit_id_bantuan').value = data.id_bantuan;
        document.getElementById('edit_jenis_bantuan').value = data.jenis_bantuan;
        document.getElementById('edit_tahun_diterima').value = data.tahun_diterima;
        document.getElementById('edit_periode').value = data.periode;
        document.getElementById('edit_tanggal_penerimaan').value = data.tanggal_penerimaan;
        document.getElementById('edit_nominal').value = data.nominal;
        document.getElementById('edit_keterangan').value = data.keterangan;

        document.getElementById('modal-edit-bantuan').classList.remove('hidden');
    }
</script>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>

<script>
    function switchTab(tabId, btnElement) {
        // 1. Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });

        // 2. Remove active state from all buttons & icons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-primary', 'text-primary', 'bg-blue-50/50');
            btn.classList.add('border-transparent', 'text-slate-500');

            let icon = btn.querySelector('.tab-icon');
            if (icon) {
                icon.classList.remove('text-primary');
                icon.classList.add('text-slate-400');
            }
        });

        // 3. Show targeted tab content
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        document.getElementById('tab-' + tabId).classList.add('block');

        // 4. Add active state to clicked button
        btnElement.classList.add('border-primary', 'text-primary', 'bg-blue-50/50');
        btnElement.classList.remove('border-transparent', 'text-slate-500');

        let activeIcon = btnElement.querySelector('.tab-icon');
        if (activeIcon) {
            activeIcon.classList.add('text-primary');
            activeIcon.classList.remove('text-slate-400');
        }
        
        // Update URL parameter without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    }

    // Handle initial active tab from URL on page load
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            // Find the button that corresponds to this tabId
            // The onclick contains switchTab('tabId', this)
            const activeBtn = document.querySelector(`button[onclick*="switchTab('${tabParam}'"]`);
            if (activeBtn) {
                // Remove initial active states hardcoded in PHP
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('border-primary', 'text-primary', 'bg-blue-50/50');
                    btn.classList.add('border-transparent', 'text-slate-500');
                    
                    let icon = btn.querySelector('.tab-icon');
                    if (icon) {
                        icon.classList.remove('text-primary');
                        icon.classList.add('text-slate-400');
                    }
                });
                
                document.querySelectorAll('.tab-content').forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('block');
                });
                
                // Simulate click logic manually
                activeBtn.classList.add('border-primary', 'text-primary', 'bg-blue-50/50');
                activeBtn.classList.remove('border-transparent', 'text-slate-500');
                
                let activeIcon = activeBtn.querySelector('.tab-icon');
                if (activeIcon) {
                    activeIcon.classList.add('text-primary');
                    activeIcon.classList.remove('text-slate-400');
                }
                
                const contentEl = document.getElementById('tab-' + tabParam);
                if(contentEl) {
                    contentEl.classList.remove('hidden');
                    contentEl.classList.add('block');
                }
            }
        }
    });

    // Edit Toggle Functions
    function toggleEdit(tab) {
        const fs = document.getElementById('fs-' + tab);
        const btnEdit = document.getElementById('btn-edit-' + tab);
        const actions = document.getElementById('action-' + tab);
        
        if(fs && btnEdit && actions) {
            fs.removeAttribute('disabled');
            btnEdit.classList.add('hidden');
            actions.classList.remove('hidden');
        }
    }

    function cancelEdit(tab) {
        const fs = document.getElementById('fs-' + tab);
        const btnEdit = document.getElementById('btn-edit-' + tab);
        const actions = document.getElementById('action-' + tab);
        
        if(fs && btnEdit && actions) {
            fs.setAttribute('disabled', 'disabled');
            btnEdit.classList.remove('hidden');
            actions.classList.add('hidden');
            
            const form = document.getElementById('form-' + tab);
            if(form) form.reset();
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>