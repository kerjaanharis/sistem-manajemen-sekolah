<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    $_SESSION['error_msg'] = "Akses ditolak. Hanya Administrator yang dapat mengakses Pengaturan Sistem.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Data Master Tahun Ajaran';
$current_page = 'pengaturan';

// Tampilkan alert jika ada
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Ambil seluruh data master TA
$stmt_ta = $pdo->query("SELECT * FROM master_tahun_ajaran ORDER BY tahun_ajaran DESC, semester DESC");
$data_ta = $stmt_ta->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Master Tahun Ajaran</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola data tahun ajaran dan atur semester yang sedang berjalan.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Form Tambah Data Baru -->
    <div class="lg:col-span-1 border-r border-slate-100 pr-0 lg:pr-6 mb-6 lg:mb-0">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-24">
            <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header">
                <h3 class="font-bold text-slate-800"><i class="fas fa-plus-circle text-primary mr-2"></i> Tambah
                    Semester Baru</h3>
            </div>

            <form action="proses.php" method="POST" class="p-6">
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
                            placeholder="Cth: 2024/2025" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="semester" class="block text-sm font-semibold text-slate-700 mb-2">Semester</label>
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
            <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header flex justify-between items-center">
                <h3 class="font-bold text-slate-800"><i class="fas fa-list text-primary mr-2"></i> Daftar Tahun Ajaran
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Tahun Ajaran</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Semester</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Status PINTU</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_ta)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                    <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                                    Belum ada data Tahun Ajaran yang didaftarkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data_ta as $ta): ?>
                                <tr class="bg-white border-b border-slate-50 hover:bg-slate-50/70 transition-colors">
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
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
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
                                            <span class="text-xs text-slate-400 italic font-medium"><i class="fas fa-lock mr-1"></i>
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

</div>

<script>
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
</script>

<?php require_once '../../includes/footer.php'; ?>