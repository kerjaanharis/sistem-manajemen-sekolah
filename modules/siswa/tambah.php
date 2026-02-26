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

$page_title = 'Tambah Data Siswa';
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
            <span class="text-sm font-medium text-slate-700">Tambah Baru</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Siswa Baru</h2>
    </div>
    <a href="index.php"
        class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg shadow-sm hover:bg-slate-50 transition-colors text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-4xl">
    <div class="p-6 sm:p-8">
        <form action="proses.php?action=add" method="POST" class="space-y-6">

            <!-- Section 1: Data Utama -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i> Informasi Identitas
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nis">NIS <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nis" name="nis" required placeholder="Contoh: 12345"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nisn">NISN</label>
                        <input type="text" id="nisn" name="nisn" placeholder="Contoh: 004123..."
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="nama_lengkap">Nama Lengkap
                            Siswa <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required
                            placeholder="Masukkan nama lengkap siswa..."
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-4 h-[42px]">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="jenis_kelamin" value="L" required
                                    class="w-4 h-4 text-primary bg-slate-100 border-slate-300 focus:ring-primary focus:ring-2">
                                <span
                                    class="ml-2 text-sm font-medium text-slate-600 group-hover:text-slate-800">Laki-laki</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="jenis_kelamin" value="P" required
                                    class="w-4 h-4 text-pink-500 bg-slate-100 border-slate-300 focus:ring-pink-500 focus:ring-2">
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
                    <i class="fas fa-graduation-cap text-primary mr-2"></i> Akademik & Sistem
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="kelas">Kelas <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="kelas" name="kelas" required placeholder="Contoh: XII-RPL-1"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="angkatan">Tahun
                            Angkatan</label>
                        <input type="number" id="angkatan" name="angkatan" min="2000" max="2099" step="1"
                            placeholder="<?= date('Y') ?>" value="<?= date('Y') ?>"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="rfid_tag">Kode Kartu RFID
                            (Opsional)</label>
                        <div class="relative">
                            <i class="fas fa-wifi absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="rfid_tag" name="rfid_tag" placeholder="Tap kartu di sini..."
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                    </div>

                    <div class="sm:col-span-2 p-4 rounded-xl bg-indigo-50 border border-indigo-100 mt-2">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="create_account" name="create_account" type="checkbox" value="1"
                                    class="w-4 h-4 border border-slate-300 rounded bg-white focus:ring-3 focus:ring-primary text-primary"
                                    checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="create_account" class="font-bold text-indigo-800 cursor-pointer">Buatkan
                                    Akun SSO Otomatis</label>
                                <p class="text-indigo-600/80 mt-1">Jika dicentang, sistem akan otomatis membuat akun SSO
                                    dengan username menggunakan <b>NIS</b> dan kata sandi menggunakan <b>NIS</b> siswa
                                    tersebut. Role akan otomatis diset sebagai Siswa.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <button type="reset"
                    class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-colors text-sm font-semibold">
                    Reset Form
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-blue-800 transition-all focus:ring-4 focus:ring-primary/30 shadow-md hover:shadow-lg text-sm font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Data Siswa
                </button>
            </div>

        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>