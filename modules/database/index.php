<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role_name']) !== 'administrator') {
    $_SESSION['error_msg'] = "Akses ditolak. Hanya Administrator yang dapat mengakses Manajemen Database.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Manajemen Database';
$current_page = 'database';

// Tampilkan alert jika ada
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Manajemen Database</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola data sistem, mulai dari backup, restore, hingga reset database.
        </p>
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Card Backup Database (Left) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
        <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header flex items-center gap-3">
            <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-download text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">Backup Database</h3>
                <p class="text-xs text-slate-500">Unduh cadangan data sistem</p>
            </div>
        </div>
        <div class="p-6 flex-1 text-sm text-slate-600 text-justify">
            <p class="mb-4">Fitur ini memungkinkan Anda untuk mengunduh seluruh struktur dan data yang ada pada sistem
                (file .sql). Sangat disarankan untuk rutin melakukan backup sebelum melakukan perubahan besar atau pada
                akhir periode.</p>
        </div>
        <div class="p-6 pt-0 border-t border-slate-50 mt-auto bg-white">
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="backup">
                <button type="submit"
                    class="w-full mt-4 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-colors shadow-sm">
                    <i class="fas fa-cloud-download-alt mr-2"></i> Mulai Backup Data
                </button>
            </form>
        </div>
    </div>

    <!-- Card Restore Database (Center) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
        <div class="border-b border-slate-100 bg-emerald-50/50 p-5 content-header flex items-center gap-3">
            <div
                class="bg-emerald-100 text-emerald-600 w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-upload text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">Restore Database</h3>
                <p class="text-xs text-slate-500">Pulihkan data dari cadangan</p>
            </div>
        </div>

        <form action="proses.php" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1">
            <div class="p-6 flex-1 text-sm text-slate-600 text-justify">
                <p class="mb-4">Pilih file SQL hasil backup sebelumnya untuk memulihkan sistem anda. <span
                        class="text-red-600 font-semibold">Tindakan ini akan menimpa data yang ada di sistem saat ini
                        dengan data dari file backup.</span></p>
                <input type="hidden" name="aksi" value="restore">
                <div class="mb-4 mt-2">
                    <label class="block mb-2 text-sm font-medium text-slate-900" for="file_backup">File Backup
                        (.sql)</label>
                    <input
                        class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-700"
                        id="file_backup" name="file_backup" type="file" accept=".sql" required>
                </div>
            </div>
            <div class="p-6 pt-0 border-t border-slate-50 mt-auto bg-white">
                <button type="button"
                    onclick="confirmAksi(event, this.form, 'Peringatan Restore', 'Apakah Anda yakin ingin memulihkan database? Seluruh data saat ini akan diganti dengan data dari file backup ini.', 'Ya, Pulihkan Data', '#10b981')"
                    class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-colors shadow-sm">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Mulai Restore Data
                </button>
            </div>
        </form>
    </div>

    <!-- Card Reset Database (Right) -->
    <div
        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full border-t-4 border-t-red-500">
        <div class="border-b border-slate-100 bg-red-50/50 p-5 content-header flex items-center gap-3">
            <div class="bg-red-100 text-red-600 w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                <i class="fas fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">Reset Database</h3>
                <p class="text-xs text-slate-500">Kosongkan seluruh data sistem</p>
            </div>
        </div>
        <div class="p-6 flex-1 text-sm text-slate-600 text-justify">
            <div class="p-3 mb-4 text-xs text-red-800 rounded bg-red-50 border border-red-200">
                <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Sangat Berbahaya!</strong> Tindakan ini tidak
                dapat dibatalkan.
            </div>
            <p>Fitur ini akan menghapus/mengosongkan seluruh data siswa, nilai, log, dan pengguna dari sistem.
                Pengaturan utama, master data, dan satu akun Administrator default terpisah akan dipertahankan sehingga
                Anda dapat login kembali.</p>
        </div>
        <div class="p-6 pt-0 border-t border-slate-50 mt-auto bg-white">
            <form action="proses.php" method="POST">
                <input type="hidden" name="aksi" value="reset">
                <button type="button"
                    onclick="confirmAksi(event, this.form, 'Peringatan Reset Sistem', 'TINDAKAN INI SANGAT BERBAHAYA! Semua data operasional akan dihapus permanen dan tidak dapat dikembalikan. Lanjutkan?', 'Ya, Hapus Semua Data', '#ef4444')"
                    class="w-full mt-4 text-red-500 bg-red-50 hover:bg-red-600 hover:text-white focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-3 flex items-center justify-center transition-colors shadow-sm">
                    <i class="fas fa-trash-alt mr-2"></i> Reset Sistem
                </button>
            </form>
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