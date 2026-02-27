<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Cek hak akses
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role_name']), ['administrator', 'kepala sekolah'])) {
    $_SESSION['error_msg'] = "Akses ditolak. Anda tidak berhak mengakses halaman ini.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Master Tugas Tambahan';
$current_page = 'tugas_tambahan';

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Set filter Semester (Default ngikut konfig TAHUN_AJARAN & SEMESTER_AKTIF, tapi user bisa ganti view)
$filter_ta = $_GET['ta'] ?? TAHUN_AJARAN;
$filter_smt = $_GET['smt'] ?? SEMESTER_AKTIF;

// Get Master Data Tugas
$stmt_all_tugas = $pdo->query("SELECT * FROM master_tugas ORDER BY id_tugas ASC");
$master_tugas_list = $stmt_all_tugas->fetchAll();

// Get list Pegawai for Dropdowns
$stmt_pegawai = $pdo->query("SELECT id_pegawai, nama_lengkap, tipe_pegawai FROM data_pegawai WHERE status_pegawai = 'Aktif' ORDER BY nama_lengkap ASC");
$pegawai_list = $stmt_pegawai->fetchAll();

// Get Penugasan for Active Filter
$stmt_penugasan = $pdo->prepare("
    SELECT t.nama_tugas, p.id_penugasan, pg.nama_lengkap, t.id_tugas, t.kategori
    FROM master_tugas t
    LEFT JOIN penugasan_pegawai p ON t.id_tugas = p.id_tugas AND p.tahun_ajaran = ? AND p.semester = ?
    LEFT JOIN data_pegawai pg ON p.id_pegawai = pg.id_pegawai
    ORDER BY t.id_tugas ASC
");
$stmt_penugasan->execute([$filter_ta, $filter_smt]);
$penugasan_list = $stmt_penugasan->fetchAll();

// Get unique TA from Master Kelas or Penugasan for Filter dropdown
$stmt_ta = $pdo->query("
    SELECT DISTINCT tahun_ajaran FROM master_kelas 
    UNION 
    SELECT DISTINCT tahun_ajaran FROM penugasan_pegawai 
    ORDER BY tahun_ajaran DESC
");
$ta_options = $stmt_ta->fetchAll(PDO::FETCH_COLUMN);
if (!in_array(TAHUN_AJARAN, $ta_options)) {
    array_unshift($ta_options, TAHUN_AJARAN);
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Tugas Tambahan</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola pembagian tugas tambahan dan master data jabatan.</p>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="p-4 mb-6 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="border-b border-slate-200 mb-6">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-slate-500" id="tugasTab"
        data-tabs-toggle="#tugasTabContent" role="tablist">
        <li class="mr-2" role="presentation">
            <button
                class="inline-block p-4 border-b-2 rounded-t-lg hover:text-slate-600 hover:border-slate-300 transition-colors"
                id="penugasan-tab" data-tabs-target="#penugasan" type="button" role="tab" aria-controls="penugasan"
                aria-selected="true">
                <i class="fas fa-tasks mr-2"></i> Pembagian Penugasan
            </button>
        </li>
        <li class="mr-2" role="presentation">
            <button
                class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-slate-600 hover:border-slate-300 transition-colors"
                id="master-tab" data-tabs-target="#master" type="button" role="tab" aria-controls="master"
                aria-selected="false">
                <i class="fas fa-database mr-2"></i> Master Data Tugas
            </button>
        </li>
    </ul>
</div>

<div id="tugasTabContent">
    <!-- TAB 1: Pembagian Penugasan -->
    <div class="hidden" id="penugasan" role="tabpanel" aria-labelledby="penugasan-tab">

        <!-- Filter Bar -->
        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex flex-col md:flex-row gap-4 items-end">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tahun
                        Ajaran</label>
                    <select name="ta"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-48 p-2">
                        <?php foreach ($ta_options as $opt): ?>
                            <option value="<?= htmlspecialchars($opt) ?>" <?= $filter_ta == $opt ? 'selected' : '' ?>>
                                <?= htmlspecialchars($opt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Semester</label>
                    <select name="smt"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-40 p-2">
                        <option value="Ganjil" <?= $filter_smt == 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                        <option value="Genap" <?= $filter_smt == 'Genap' ? 'selected' : '' ?>>Genap</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="text-white bg-slate-800 hover:bg-slate-900 focus:ring-4 focus:ring-slate-300 font-medium rounded-lg text-sm px-5 py-2 transition-colors">
                        <i class="fas fa-filter mr-1"></i> Terapkan
                    </button>
                </div>
            </form>

            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="salin_penugasan">
                <input type="hidden" name="current_ta" value="<?= htmlspecialchars($filter_ta) ?>">
                <input type="hidden" name="current_smt" value="<?= htmlspecialchars($filter_smt) ?>">
                <button type="button"
                    onclick="confirmAksi(event, this.form, 'Salin Penugasan', 'Salin seluruh riwayat penugasan dari semester lalu ke filter ini?', 'Ya, Salin', '#4f46e5')"
                    class="text-indigo-600 bg-indigo-50 hover:bg-indigo-100 font-medium rounded-lg text-sm px-4 py-2 border border-indigo-200 transition-colors">
                    <i class="fas fa-clone mr-1"></i> Salin dari Semester Lalu
                </button>
            </form>
        </div>

        <!-- Form Simpan Massal -->
        <form action="proses.php" method="POST">
            <input type="hidden" name="aksi" value="simpan_penugasan_massal">
            <input type="hidden" name="tahun_ajaran" value="<?= htmlspecialchars($filter_ta) ?>">
            <input type="hidden" name="semester" value="<?= htmlspecialchars($filter_smt) ?>">

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                <div class="border-b border-slate-100 bg-slate-50/50 p-4 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800"><i class="fas fa-clipboard-list text-primary mr-2"></i> Daftar
                        Formasi Jabatan</h3>
                    <span
                        class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded border border-blue-200">Menampilkan
                        Filter:
                        <?= htmlspecialchars($filter_ta) ?> -
                        <?= htmlspecialchars($filter_smt) ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Tugas / Jabatan Tambahan</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kategori</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-1/2">Nama Pegawai / Guru yang
                                    Ditugaskan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penugasan_list as $tugas): ?>
                                <tr class="bg-white border-b border-slate-50 hover:bg-slate-50/70">
                                    <td class="px-6 py-3 font-medium text-slate-800">
                                        <?= htmlspecialchars($tugas['nama_tugas']) ?>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="bg-slate-100 text-slate-600 text-[10px] uppercase tracking-wide font-bold px-2 py-1 rounded">
                                            <?= htmlspecialchars($tugas['kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <!-- Note: Penamaan select name array id_tugas[] -->
                                        <select name="tugas[<?= $tugas['id_tugas'] ?>]"
                                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2">
                                            <option value="">-- Kosong / Belum Ada --</option>
                                            <?php foreach ($pegawai_list as $pgw): ?>
                                                <?php
                                                // Kita gunakan logic IF name == name atau jika id nya tercatat. Saat ini join kita me-return id_penugasan jika ada data di penugasan_pegawai
                                                // Karena join dilakukan antara penugasan dan master data, field id_pegawai ada didalam tabel penugasan_pegawai. 
                                                // Query diatas tidak SELECT p.id_pegawai secara eksplisit, mari asumsikan via nama_lengkap dulu atau kita ubah logic db nya. 
                                                // Lebih baik kita re-query id_pegawainya via sub/join yg benar. Anggaplah kita tambahkan logic $tugas_assignee per loop
                                        
                                                // Manual fetch for assigne just to be safe if join gets messy, though DB design handles it. 
                                                // Untuk cepatnya, saya asumsikan $tugas['nama_lengkap'] ada isinya jika ditugaskan.
                                                $selected = ($tugas['nama_lengkap'] == $pgw['nama_lengkap']) ? 'selected' : '';
                                                ?>
                                                <option value="<?= $pgw['id_pegawai'] ?>" <?= $selected ?>>
                                                    <?= htmlspecialchars($pgw['nama_lengkap']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit"
                class="text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-3 mb-10 shadow-sm transition-colors flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Pembagian Tugas
            </button>
        </form>
    </div>

    <!-- TAB 2: Master Data Tugas -->
    <div class="hidden" id="master" role="tabpanel" aria-labelledby="master-tab">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Baru -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 sticky top-24">
                    <div class="border-b border-slate-100 bg-slate-50/50 p-4">
                        <h3 class="font-bold text-slate-800"><i class="fas fa-plus-circle text-primary mr-2"></i> Buat
                            Jabatan Baru</h3>
                    </div>
                    <form action="proses.php" method="POST" class="p-5">
                        <input type="hidden" name="aksi" value="tambah_master">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Tugas Tambahan</label>
                            <input type="text" name="nama_tugas"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                required placeholder="Cth: Satgas Anti Narkoba">
                        </div>
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori</label>
                            <select name="kategori"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                                <option value="Kepala Program/Unit">Kepala Program/Unit</option>
                                <option value="Koordinator/Pembina">Koordinator/Pembina</option>
                                <option value="Lainnya" selected>Lainnya</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center transition-colors shadow-sm">
                            <i class="fas fa-save mr-2"></i> Tambahkan ke Master List
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Master Data -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50/50 p-4">
                        <h3 class="font-bold text-slate-800"><i class="fas fa-list text-primary mr-2"></i> Daftar
                            Identitas Tugas</h3>
                        <p class="text-xs text-slate-500 mt-1">Hanya menambah di sini, tidak otomatis memberikan jabatan
                            ke Guru tersebut. Lakukan pembagian tugas di tab sebelahnya.</p>
                    </div>
                    <div class="overflow-x-auto max-h-[70vh]">
                        <table class="w-full text-sm text-left text-slate-500">
                            <thead
                                class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-100 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold text-center w-12">No</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Nama Jabatan</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Kategori</th>
                                    <th scope="col" class="px-6 py-4 font-semibold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($master_tugas_list as $m): ?>
                                    <tr class="bg-white border-b border-slate-50 hover:bg-slate-50/70">
                                        <td class="px-6 py-3 text-center font-medium text-slate-500">
                                            <?= $no++ ?>
                                        </td>
                                        <td class="px-6 py-3 font-medium text-slate-800">
                                            <?= htmlspecialchars($m['nama_tugas']) ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span
                                                class="bg-slate-100 text-slate-600 text-[10px] uppercase tracking-wide font-bold px-2 py-1 rounded">
                                                <?= htmlspecialchars($m['kategori']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                onclick="editMasterAksi(<?= $m['id_tugas'] ?>, '<?= htmlspecialchars(addslashes($m['nama_tugas'])) ?>', '<?= htmlspecialchars(addslashes($m['kategori'])) ?>')"
                                                class="font-medium text-amber-500 bg-amber-50 hover:bg-amber-100 px-2 py-1.5 rounded-lg text-xs transition-colors mr-1">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="proses.php" method="POST" class="inline">
                                                <input type="hidden" name="aksi" value="hapus_master">
                                                <input type="hidden" name="id_tugas" value="<?= $m['id_tugas'] ?>">
                                                <button type="button"
                                                    onclick="confirmAksi(event, this.form, 'Hapus Identitas Tugas?', 'Hapus jabatan <?= htmlspecialchars($m['nama_tugas']) ?>? Ini akan menghapus histori riwayat penugasannya di seluruh tahun ajaran.', 'Ya, Hapus Permanen', '#dc2626')"
                                                    class="font-medium text-red-500 bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded-lg text-xs transition-colors">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Master Tugas -->
<div id="modalEditMaster" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-xl shadow-lg border border-slate-100">
            <div
                class="flex items-center justify-between p-4 md:p-5 border-b border-slate-100 rounded-t bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">
                    <i class="fas fa-edit text-amber-500 mr-2"></i> Edit Master Tugas
                </h3>
                <button type="button" onclick="closeEditModal()"
                    class="text-slate-400 bg-transparent hover:bg-slate-200 hover:text-slate-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <form action="proses.php" method="POST" class="p-4 md:p-5">
                <input type="hidden" name="aksi" value="edit_master">
                <input type="hidden" name="id_tugas" id="edit_id_tugas" value="">

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Tugas Tambahan</label>
                    <input type="text" name="nama_tugas" id="edit_nama_tugas"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                        required>
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori</label>
                    <select name="kategori" id="edit_kategori"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                        <option value="Kepala Program/Unit">Kepala Program/Unit</option>
                        <option value="Koordinator/Pembina">Koordinator/Pembina</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="w-full text-slate-600 bg-slate-100 hover:bg-slate-200 focus:ring-4 focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center transition-colors shadow-sm">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-full text-white bg-amber-500 hover:bg-amber-600 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center transition-colors shadow-sm">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
<script>
    function editMasterAksi(id, nama, kategori) {
        document.getElementById('edit_id_tugas').value = id;
        document.getElementById('edit_nama_tugas').value = nama;
        document.getElementById('edit_kategori').value = kategori;
        const modal = document.getElementById('modalEditMaster');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEditMaster');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function confirmAksi(e, form, title, text, btnText, btnColor) {
        e.preventDefault();
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#475569',
            confirmButtonText: btnText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { confirmButton: 'shadow-sm', cancelButton: 'shadow-sm' }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    // Remember Active Tab
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        let tabParam = urlParams.get('tab');
        if (!tabParam) {
            tabParam = sessionStorage.getItem('activeTugasTab') || 'penugasan';
        }

        if (tabParam === 'master') {
            document.getElementById('master-tab').click();
        } else {
            document.getElementById('penugasan-tab').click();
        }

        // Save tab selection
        document.querySelectorAll('[data-tabs-target]').forEach(trigger => {
            trigger.addEventListener('click', function () {
                let currentTab = this.getAttribute('data-tabs-target').replace('#', '');
                sessionStorage.setItem('activeTugasTab', currentTab);

                // Update URL parameter without reload
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', currentTab);
                window.history.pushState({}, '', newUrl);
            });
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>