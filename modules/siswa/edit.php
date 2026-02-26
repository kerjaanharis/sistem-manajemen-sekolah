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

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT s.*, u.username FROM data_siswa s LEFT JOIN users u ON s.id_user = u.id_user WHERE s.id_siswa = :id");
    $stmt->execute(['id' => $id]);
    $siswa = $stmt->fetch();

    if (!$siswa) {
        $_SESSION['error_msg'] = "Data siswa tidak ditemukan!";
        header("Location: index.php");
        exit;
    }
} catch (\PDOException $e) {
    $_SESSION['error_msg'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

$page_title = 'Edit Data Siswa';
$current_page = 'siswa';

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="index.php" class="text-sm font-medium text-slate-400 hover:text-primary transition-colors">Data
                Siswa</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-sm font-medium text-slate-700">Edit Data</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Edit Profil Siswa</h2>
    </div>
    <a href="index.php"
        class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg shadow-sm hover:bg-slate-50 transition-colors text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-4xl">
    <div class="p-6 sm:p-8">
        <form action="proses.php?action=edit" method="POST" class="space-y-6">
            <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">
            <input type="hidden" name="id_user" value="<?= $siswa['id_user'] ?>">

            <!-- Section 1: Data Utama -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i> Informasi Identitas
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nis">NIS <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nis" name="nis" value="<?= htmlspecialchars($siswa['nis']) ?>" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nisn">NISN</label>
                        <input type="text" id="nisn" name="nisn" value="<?= htmlspecialchars($siswa['nisn'] ?? '') ?>"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nama_lengkap">Nama Lengkap
                            Siswa <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            value="<?= htmlspecialchars($siswa['nama_lengkap']) ?>" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-4 h-[38px]">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="jenis_kelamin" value="L" required
                                    <?= $siswa['jenis_kelamin'] == 'L' ? 'checked' : '' ?> class="w-4 h-4 text-primary
                                bg-slate-100 border-slate-300 focus:ring-primary focus:ring-2">
                                <span
                                    class="ml-2 text-sm font-medium text-slate-600 group-hover:text-slate-800">Laki-laki</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="jenis_kelamin" value="P" required
                                    <?= $siswa['jenis_kelamin'] == 'P' ? 'checked' : '' ?> class="w-4 h-4 text-pink-500
                                bg-slate-100 border-slate-300 focus:ring-pink-500 focus:ring-2">
                                <span
                                    class="ml-2 text-sm font-medium text-slate-600 group-hover:text-slate-800">Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Akademi & SSO -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 mt-8 flex items-center">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i> Akademik & Status
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="kelas">Kelas <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="kelas" name="kelas" value="<?= htmlspecialchars($siswa['kelas']) ?>"
                            required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="angkatan">Tahun
                            Angkatan</label>
                        <input type="number" id="angkatan" name="angkatan"
                            value="<?= htmlspecialchars($siswa['angkatan'] ?? '') ?>" min="2000" max="2099" step="1"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="rfid_tag">Kode Kartu
                            RFID</label>
                        <div class="relative">
                            <i class="fas fa-wifi absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="rfid_tag" name="rfid_tag"
                                value="<?= htmlspecialchars($siswa['rfid_tag'] ?? '') ?>" placeholder="Kosong..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="status_siswa">Status
                            Siswa</label>
                        <select id="status_siswa" name="status_siswa"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="Aktif" <?= $siswa['status_siswa'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Lulus" <?= $siswa['status_siswa'] == 'Lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="Pindah" <?= $siswa['status_siswa'] == 'Pindah' ? 'selected' : '' ?>>Pindah
                            </option>
                            <option value="Keluar" <?= $siswa['status_siswa'] == 'Keluar' ? 'selected' : '' ?>>Keluar DO
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SSO Status Box -->
            <div
                class="mt-8 p-5 rounded-xl border <?= $siswa['id_user'] ? 'bg-indigo-50 border-indigo-100' : 'bg-slate-50 border-slate-200' ?>">
                <h4 class="font-bold text-sm mb-2 <?= $siswa['id_user'] ? 'text-indigo-800' : 'text-slate-700' ?>">
                    Informasi Akun SSO (Login Sistem)</h4>

                <?php if ($siswa['id_user']): ?>
                    <p class="text-sm text-indigo-700 mb-3"><i class="fas fa-check-circle text-indigo-500 mr-1"></i> Siswa
                        ini telah memiliki akses SSO yang ditautkan ke akun utama.</p>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <span class="text-indigo-500 font-medium text-xs uppercase h-full mb-1 block">Username</span>
                            <span
                                class="font-semibold text-indigo-900 bg-white px-3 py-1 rounded shadow-sm border border-indigo-100">
                                <?= htmlspecialchars($siswa['username']) ?>
                            </span>
                        </div>
                        <div class="self-end pb-1 text-xs text-indigo-400">Hubungi Administrator untuk mereset sandi.</div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-600 mb-3"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>
                        Data siswa ini belum tertaut dengan akun login SSO.</p>
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="create_account" name="create_account" type="checkbox" value="1"
                                class="w-4 h-4 border border-slate-300 rounded bg-white focus:ring-3 focus:ring-primary text-primary">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="create_account" class="font-bold hover:text-primary cursor-pointer">Buatkan Akun SSO
                                Otomatis Sekarang</label>
                            <p class="text-slate-500 mt-0.5 text-xs">Akan dibuatkan dengan username berdasarkan NIS saat
                                ini.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-6 flex justify-end">
                <button type="submit"
                    class="px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all focus:ring-4 focus:ring-amber-500/30 shadow-md hover:shadow-lg text-sm font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Perbarui Data Siswa
                </button>
            </div>

        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>