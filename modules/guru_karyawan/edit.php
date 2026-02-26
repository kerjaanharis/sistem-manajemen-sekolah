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
    $stmt = $pdo->prepare("SELECT p.*, u.username, r.nama_role FROM data_pegawai p 
                           LEFT JOIN users u ON p.id_user = u.id_user 
                           LEFT JOIN roles r ON u.id_role = r.id_role
                           WHERE p.id_pegawai = :id");
    $stmt->execute(['id' => $id]);
    $pegawai = $stmt->fetch();

    if (!$pegawai) {
        $_SESSION['error_msg'] = "Data pegawai tidak ditemukan!";
        header("Location: index.php");
        exit;
    }
} catch (\PDOException $e) {
    $_SESSION['error_msg'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

$page_title = 'Edit Data Pegawai';
$current_page = 'guru_karyawan';

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="index.php" class="text-sm font-medium text-slate-400 hover:text-emerald-500 transition-colors">Data
                Pegawai</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-sm font-medium text-slate-700">Edit Data</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Edit Profil Pegawai</h2>
    </div>
    <a href="index.php"
        class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg shadow-sm hover:bg-slate-50 transition-colors text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-4xl">
    <div class="p-6 sm:p-8">
        <form action="proses.php?action=edit" method="POST" class="space-y-6">
            <input type="hidden" name="id_pegawai" value="<?= $pegawai['id_pegawai'] ?>">
            <input type="hidden" name="id_user" value="<?= $pegawai['id_user'] ?>">

            <!-- Section 1: Data Utama -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-id-badge text-emerald-500 mr-2"></i> Informasi Identitas
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nip_nik">NIP / NIK <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nip_nik" name="nip_nik"
                            value="<?= htmlspecialchars($pegawai['nip_nik']) ?>" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nama_lengkap">Nama Lengkap
                            & Gelar <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            value="<?= htmlspecialchars($pegawai['nama_lengkap']) ?>" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Section 2: Kepegawaian & Status -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 mt-8 flex items-center">
                    <i class="fas fa-briefcase text-emerald-500 mr-2"></i> Kepegawaian & Status
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="tipe_pegawai">Tipe Pegawai
                            <span class="text-red-500">*</span></label>
                        <select id="tipe_pegawai" name="tipe_pegawai" required
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <option value="Guru" <?= $pegawai['tipe_pegawai'] == 'Guru' ? 'selected' : '' ?>>Guru / Tenaga
                                Pendidik</option>
                            <option value="Karyawan" <?= $pegawai['tipe_pegawai'] == 'Karyawan' ? 'selected' : '' ?>
                                >Karyawan / Staf TU</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Mengubah ini juga akan menyesuaikan Role SSO jika akun
                            ditautkan.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="jabatan">Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan"
                            value="<?= htmlspecialchars($pegawai['jabatan'] ?? '') ?>"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="rfid_tag">Kode Kartu
                            RFID</label>
                        <div class="relative">
                            <i class="fas fa-wifi absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="rfid_tag" name="rfid_tag"
                                value="<?= htmlspecialchars($pegawai['rfid_tag'] ?? '') ?>" placeholder="Kosong..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="status_pegawai">Status
                            Kepegawaian</label>
                        <select id="status_pegawai" name="status_pegawai"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <option value="Aktif" <?= $pegawai['status_pegawai'] == 'Aktif' ? 'selected' : '' ?>>Aktif
                            </option>
                            <option value="Cuti" <?= $pegawai['status_pegawai'] == 'Cuti' ? 'selected' : '' ?>>Cuti
                            </option>
                            <option value="Mutasi" <?= $pegawai['status_pegawai'] == 'Mutasi' ? 'selected' : '' ?>>Mutasi
                            </option>
                            <option value="Pensiun" <?= $pegawai['status_pegawai'] == 'Pensiun' ? 'selected' : '' ?>
                                >Pensiun</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SSO Status Box -->
            <div
                class="mt-8 p-5 rounded-xl border <?= $pegawai['id_user'] ? 'bg-indigo-50 border-indigo-100' : 'bg-slate-50 border-slate-200' ?>">
                <h4 class="font-bold text-sm mb-2 <?= $pegawai['id_user'] ? 'text-indigo-800' : 'text-slate-700' ?>">
                    Informasi Akun SSO (Login Sistem)</h4>

                <?php if ($pegawai['id_user']): ?>
                    <p class="text-sm text-indigo-700 mb-3"><i class="fas fa-check-circle text-indigo-500 mr-1"></i> Telah
                        memiliki akses SSO yang ditautkan ke akun utama.</p>
                    <div class="flex flex-wrap gap-4">
                        <div class="text-sm">
                            <span class="text-indigo-500 font-medium text-xs uppercase h-full mb-1 block">Username</span>
                            <span
                                class="font-semibold text-indigo-900 bg-white px-3 py-1 rounded shadow-sm border border-indigo-100">
                                <?= htmlspecialchars($pegawai['username']) ?>
                            </span>
                        </div>
                        <div class="text-sm">
                            <span class="text-indigo-500 font-medium text-xs uppercase h-full mb-1 block">Role Akses</span>
                            <span
                                class="font-semibold text-indigo-900 bg-white px-3 py-1 rounded shadow-sm border border-indigo-100">
                                <?= htmlspecialchars($pegawai['nama_role']) ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-600 mb-3"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>
                        Pegawai ini belum tertaut dengan akun login SSO.</p>
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="create_account" name="create_account" type="checkbox" value="1"
                                class="w-4 h-4 border border-slate-300 rounded bg-white focus:ring-3 focus:ring-emerald-500 text-emerald-600">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="create_account" class="font-bold hover:text-emerald-700 cursor-pointer">Buatkan Akun
                                SSO Otomatis</label>
                            <p class="text-slate-500 mt-0.5 text-xs">Akan dibuatkan dengan username berdasarkan NIP/NIK saat
                                ini.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-6 flex justify-end">
                <button type="submit"
                    class="px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all focus:ring-4 focus:ring-amber-500/30 shadow-md hover:shadow-lg text-sm font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Perbarui Data Pegawai
                </button>
            </div>

        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>