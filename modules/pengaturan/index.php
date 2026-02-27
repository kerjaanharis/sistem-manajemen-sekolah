<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    $_SESSION['error_msg'] = "Akses ditolak. Hanya Administrator yang dapat mengakses Pengaturan Sistem.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Pengaturan Sistem';
$current_page = 'pengaturan';

// Tampilkan alert jika ada
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Ambil seluruh data master TA
$stmt_ta = $pdo->query("SELECT * FROM master_tahun_ajaran ORDER BY tahun_ajaran DESC, semester DESC");
$data_ta = $stmt_ta->fetchAll();

// Ambil data pengaturan sekolah
$stmt_sekolah = $pdo->query("SELECT * FROM pengaturan_sekolah WHERE id = 1");
$sekolah = $stmt_sekolah->fetch(PDO::FETCH_ASSOC) ?: [];

// Ambil data roles
$stmt_roles = $pdo->query("SELECT * FROM roles ORDER BY id_role ASC");
$data_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Pengaturan Sistem</h2>
        <p class="text-sm text-slate-500 mt-1">Konfigurasi data induk sekolah, akses, tugas tambahan, dan tahun ajaran.
        </p>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="p-4 mb-6 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- Main Tabs Section -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">

    <!-- Tab Navigation -->
    <div class="border-b border-slate-100 bg-slate-50/50 px-2 sm:px-4 overflow-x-auto scrollbar-hide">
        <nav class="-mb-px flex space-x-2 sm:space-x-4 min-w-max" aria-label="Tabs">
            <!-- Tabs Item -->
            <?php
            $tabs = [
                'sekolah' => ['ikon' => 'fa-school', 'label' => '1. Seting Data Sekolah'],
                'tugas' => ['ikon' => 'fa-briefcase', 'label' => '2. Seting Data Tugas'],
                'role' => ['ikon' => 'fa-user-shield', 'label' => '3. Seting Role Akses'],
                'ta' => ['ikon' => 'fa-calendar-alt', 'label' => '4. Seting Tahun Ajaran'],
            ];

            foreach ($tabs as $id => $tab):
                // Make 'sekolah' the default active tab
                $activeClass = ($id === 'sekolah') ? 'border-primary text-primary bg-blue-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300';
                ?>
                <button onclick="switchTab('<?= $id ?>', this)"
                    class="tab-btn <?= $activeClass ?> whitespace-nowrap py-4 px-3 sm:px-4 border-b-2 font-medium text-sm transition-colors flex items-center">
                    <i
                        class="fas <?= $tab['ikon'] ?> mr-2 <?= ($id === 'sekolah') ? 'text-primary' : 'text-slate-400' ?> tab-icon transition-colors"></i>
                    <?= $tab['label'] ?>
                </button>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="p-6">

        <!-- TAB: Data Sekolah -->
        <div id="tab-sekolah" class="tab-content block animate-fadeIn">
            <?php require_once 'form_sekolah.php'; ?>
        </div>

        <!-- TAB: Data Tugas -->
        <div id="tab-tugas" class="tab-content hidden animate-fadeIn">
            <h4 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2 flex items-center">
                <i class="fas fa-briefcase text-primary mr-2"></i> Pengaturan Data Tugas Kepanitiaan
            </h4>
            <div class="text-center py-16 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                <div
                    class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-slate-100 text-slate-300">
                    <i class="fas fa-briefcase text-3xl"></i>
                </div>
                <h5 class="text-slate-700 font-bold mb-1">Modul Dalam Pengembangan</h5>
                <p class="text-sm text-slate-500 max-w-md mx-auto">
                    Manajemen referensi jenis tugas tambahan tenaga pendidik akan diatur melalui halaman ini.
                </p>
            </div>
        </div>

        <!-- TAB: Role Akses -->
        <div id="tab-role" class="tab-content hidden animate-fadeIn">
            <?php require_once 'form_role.php'; ?>
        </div>

        <!-- TAB: Tahun Ajaran -->
        <div id="tab-ta" class="tab-content hidden animate-fadeIn">
            <h4 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2 flex items-center">
                <i class="fas fa-calendar-alt text-primary mr-2"></i> Pengaturan Tahun Ajaran Aktif
            </h4>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Kolom Form Tambah Data Baru -->
                <div class="lg:col-span-1 border-r border-slate-100 pr-0 lg:pr-6 mb-6 lg:mb-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-24">
                        <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header">
                            <h3 class="font-bold text-slate-800"><i class="fas fa-plus-circle text-primary mr-2"></i>
                                Tambah
                                Semester Baru</h3>
                        </div>

                        <form id="form-tambah-ta" action="proses.php" method="POST" class="p-6">
                            <!-- Aksi: tambah -->
                            <input type="hidden" name="aksi" value="tambah">

                            <div class="mb-5">
                                <label for="tahun_ajaran" class="block text-sm font-semibold text-slate-700 mb-2">Tahun
                                    Ajaran</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-calendar-check text-sm"></i>
                                    </div>
                                    <input type="text" id="tahun_ajaran" name="tahun_ajaran"
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full pl-10 p-2.5 transition-colors"
                                        placeholder="Cth: 2024/2025" required pattern="^[0-9]{4}/[0-9]{4}$"
                                        title="Format harus XXXX/YYYY, contoh: 2024/2025" maxlength="9"
                                        oninput="this.value = this.value.replace(/[^0-9/]/g, '')">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="semester"
                                    class="block text-sm font-semibold text-slate-700 mb-2">Semester</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-hourglass-half text-sm"></i>
                                    </div>
                                    <select id="semester" name="semester"
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full pl-10 p-2.5 transition-colors"
                                        required>
                                        <option value="Ganjil">Ganjil</option>
                                        <option value="Genap">Genap</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center transition-colors shadow-sm">
                                <i class="fas fa-save mr-2"></i> Simpan Master Data
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kolom Tabel Daftar Data Master -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div
                            class="border-b border-slate-100 bg-slate-50/50 p-5 content-header flex justify-between items-center">
                            <h3 class="font-bold text-slate-800"><i class="fas fa-list text-primary mr-2"></i> Daftar
                                Tahun Ajaran
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-500">
                                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold text-center w-12">No</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Tahun Ajaran</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Semester</th>
                                        <th scope="col" class="px-6 py-4 font-semibold">Status PINTU</th>
                                        <th scope="col" class="px-6 py-4 font-semibold text-center">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data_ta)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                                <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                                                Belum ada data Tahun Ajaran yang didaftarkan.
                                            </td>
                                        </tr>
                                    <?php else:
                                        $no = 1;
                                        ?>
                                        <?php foreach ($data_ta as $ta): ?>
                                            <tr
                                                class="bg-white border-b border-slate-50 hover:bg-slate-50/70 transition-colors">
                                                <td class="px-6 py-4 text-center font-medium text-slate-500">
                                                    <?= $no++ ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-800">
                                                    <?= htmlspecialchars($ta['tahun_ajaran']) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?= htmlspecialchars($ta['semester']) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if ($ta['is_active'] == 1): ?>
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                                                            <span
                                                                class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                                            AKTIF BERJALAN
                                                        </span>
                                                    <?php else: ?>
                                                        <span
                                                            class="bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1 rounded-full border border-slate-200">
                                                            Tidak Aktif
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php if ($ta['is_active'] != 1): ?>
                                                        <form action="proses.php" method="POST" class="inline">
                                                            <input type="hidden" name="aksi" value="aktifkan">
                                                            <input type="hidden" name="id_ta" value="<?= $ta['id_ta'] ?>">
                                                            <button type="button"
                                                                onclick="confirmAksi(event, this.form, 'Aktivasi TA Baru', 'Apakah Anda yakin ingin mengganti Semester berjalan? Semua absen dan log ke depannya akan dilabeli dengan semester ini.', 'Ya, Aktifkan', '#2563eb')"
                                                                class="font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg text-xs transition-colors shadow-sm">
                                                                <i class="fas fa-power-off mr-1"></i> Aktifkan
                                                            </button>
                                                        </form>

                                                        <!-- Opsi Hapus -->
                                                        <form action="proses.php" method="POST" class="inline ml-1">
                                                            <input type="hidden" name="aksi" value="hapus">
                                                            <input type="hidden" name="id_ta" value="<?= $ta['id_ta'] ?>">
                                                            <button type="button"
                                                                onclick="confirmAksi(event, this.form, 'Hapus Data Master', 'Hapus opsi semester ini? Data historis TIDAK terhapus, namun tidak dapat dipilih lagi.', 'Ya, Hapus', '#dc2626')"
                                                                class="font-medium text-red-500 bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded-lg text-xs transition-colors">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-xs text-slate-400 italic font-medium"><i
                                                                class="fas fa-lock mr-1"></i>
                                                            Sedang Digunakan</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div> <!-- Close Grid cols -->
        </div> <!-- Close Tab: Tahun Ajaran -->

    </div> <!-- Close Tab Contents -->
</div> <!-- Close Main Tabs Section -->

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
            customClass: {
                confirmButton: 'shadow-sm',
                cancelButton: 'shadow-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    document.getElementById('form-tambah-ta').addEventListener('submit', function (e) {
        const inputTA = document.getElementById('tahun_ajaran').value;
        const regex = /^\d{4}\/\d{4}$/;
        if (!regex.test(inputTA)) {
            e.preventDefault();
            Swal.fire({
                title: 'Format Tidak Valid!',
                html: 'Tahun Ajaran harus berformat <b>XXXX/YYYY</b><br><span class="text-sm text-slate-500">Contoh: 2024/2025</span>',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            });
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>