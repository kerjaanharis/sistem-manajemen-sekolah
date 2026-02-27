<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Pastikan hanya admin atau kepsek yang bisa mengakses manajemen master kelas
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role_name']), ['administrator', 'kepala sekolah'])) {
    $_SESSION['error_msg'] = "Akses ditolak. Anda tidak memiliki izin untuk mengelola Master Kelas.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Master Kelas (' . TAHUN_AJARAN . ')';
$current_page = 'kelas';

// Alert handling
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Get list of teachers for "Wali Kelas" dropdown
$stmt_guru = $pdo->query("SELECT id_pegawai, nama_lengkap FROM data_pegawai WHERE tipe_pegawai = 'Guru' AND status_pegawai = 'Aktif' ORDER BY nama_lengkap ASC");
$gurus = $stmt_guru->fetchAll();

// Get list of classes for active academic year, with student counts
$stmt_kelas = $pdo->prepare("
    SELECT k.*, 
           pgj.nama_lengkap as wali_kelas_ganjil,
           pgn.nama_lengkap as wali_kelas_genap,
           COUNT(a.id_siswa) as jumlah_siswa
    FROM master_kelas k
    LEFT JOIN data_pegawai pgj ON k.id_wali_kelas_ganjil = pgj.id_pegawai
    LEFT JOIN data_pegawai pgn ON k.id_wali_kelas_genap = pgn.id_pegawai
    LEFT JOIN anggota_kelas a ON k.id_kelas = a.id_kelas
    WHERE k.tahun_ajaran = ?
    GROUP BY k.id_kelas
    ORDER BY k.tingkat ASC, k.nama_kelas ASC
");
$stmt_kelas->execute([TAHUN_AJARAN]);
$kelas_list = $stmt_kelas->fetchAll();

// Get list of previous academic years for Duplication feature
$stmt_ta_prev = $pdo->query("SELECT DISTINCT tahun_ajaran FROM master_kelas WHERE tahun_ajaran != '" . TAHUN_AJARAN . "' ORDER BY tahun_ajaran DESC");
$prev_ta_list = $stmt_ta_prev->fetchAll(PDO::FETCH_COLUMN);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Master Kelas <span
                class="bg-blue-100 text-blue-800 text-base font-semibold px-2.5 py-0.5 rounded ml-2">
                <?= TAHUN_AJARAN ?>
            </span></h2>
        <p class="text-sm text-slate-500 mt-1">Kelola rombongan belajar, tentukan wali kelas, dan atur anggota siswa.
        </p>
    </div>
    <div class="mt-4 md:mt-0">
        <a href="manage.php"
            class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex items-center shadow-sm transition-colors">
            <i class="fas fa-people-arrows mr-2"></i> Pindah / Atur Anggota Kelas
        </a>
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

<!-- Tombol Duplikat -->
<?php if (!empty($prev_ta_list)): ?>
    <div
        class="mb-6 bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-start mb-3 md:mb-0">
            <i class="fas fa-copy text-indigo-500 text-xl mt-1 mr-3"></i>
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Duplikat Kelas dari Tahun Ajaran Sebelumnya</h4>
                <p class="text-xs text-slate-600 mt-0.5">Salin struktur kelas dari tahun lalu ke T.A. <?= TAHUN_AJARAN ?>.
                    Tingkat kelas (X, XI) akan otomatis dinaikkan 1 jenjang.</p>
            </div>
        </div>
        <button onclick="document.getElementById('modalDuplikat').classList.remove('hidden')"
            class="shrink-0 text-indigo-600 bg-white border border-indigo-200 hover:bg-indigo-600 hover:text-white focus:ring-4 focus:ring-indigo-100 font-medium rounded-lg text-sm px-4 py-2 transition-colors shadow-sm">
            <i class="fas fa-clone mr-2"></i> Duplikat Kelas
        </button>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Form Tambah Data Baru -->
    <div class="lg:col-span-1 border-r border-slate-100 pr-0 lg:pr-6 mb-6 lg:mb-0">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-24">
            <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header">
                <h3 class="font-bold text-slate-800"><i class="fas fa-plus-circle text-primary mr-2"></i> Tambah
                    Kelas Baru</h3>
            </div>

            <form action="proses.php" method="POST" class="p-6">
                <!-- Aksi: tambah -->
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="tahun_ajaran" value="<?= TAHUN_AJARAN ?>">

                <div class="mb-5">
                    <label for="tingkat" class="block text-sm font-semibold text-slate-700 mb-2">Tingkat /
                        Jenjang</label>
                    <div class="relative">
                        <select id="tingkat" name="tingkat"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 transition-colors"
                            required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                            <!-- Jika SMP SD bisa pakai angka 1,2,3 dst -->
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="nama_kelas" class="block text-sm font-semibold text-slate-700 mb-2">Nama Kelas
                        Singkat</label>
                    <div class="relative">
                        <input type="text" id="nama_kelas" name="nama_kelas"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 transition-colors"
                            placeholder="Cth: X-RPL-1" required>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Gunakan format yang seragam untuk kemudahan pencarian.</p>
                </div>

                <div class="mb-5">
                    <label for="id_wali_kelas_ganjil" class="block text-sm font-semibold text-slate-700 mb-2">Wali Kelas
                        - Smt Ganjil</label>
                    <div class="relative">
                        <select id="id_wali_kelas_ganjil" name="id_wali_kelas_ganjil"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 transition-colors">
                            <option value="">-- Belum Ditentukan --</option>
                            <?php foreach ($gurus as $g): ?>
                                <option value="<?= $g['id_pegawai'] ?>">
                                    <?= htmlspecialchars($g['nama_lengkap']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="id_wali_kelas_genap" class="block text-sm font-semibold text-slate-700 mb-2">Wali Kelas
                        - Smt Genap</label>
                    <div class="relative">
                        <select id="id_wali_kelas_genap" name="id_wali_kelas_genap"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5 transition-colors">
                            <option value="">-- Belum Ditentukan --</option>
                            <?php foreach ($gurus as $g): ?>
                                <option value="<?= $g['id_pegawai'] ?>">
                                    <?= htmlspecialchars($g['nama_lengkap']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit"
                    class="w-full text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center transition-colors shadow-sm">
                    <i class="fas fa-save mr-2"></i> Simpan Kelas
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Tabel Daftar Data Master -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 p-5 content-header flex justify-between items-center">
                <h3 class="font-bold text-slate-800"><i class="fas fa-list text-primary mr-2"></i> Daftar Kelas Tahun
                    <?= TAHUN_AJARAN ?>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold text-center w-12">No</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Tingkat</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Nama Kelas</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Wali Kelas (Ganjil)</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Wali Kelas (Genap)</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Jml Siswa</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kelas_list)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                    <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                                    Belum ada kelas yang dibuat untuk Tahun Ajaran ini.
                                </td>
                            </tr>
                        <?php else:
                            $no = 1;
                            ?>
                            <?php foreach ($kelas_list as $kls): ?>
                                <tr class="bg-white border-b border-slate-50 hover:bg-slate-50/70 transition-colors">
                                    <td class="px-6 py-4 text-center font-medium text-slate-500">
                                        <?= $no++ ?>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-700">
                                        <?= htmlspecialchars($kls['tingkat']) ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        <a href="manage.php#list-kls-<?= $kls['id_kelas'] ?>"
                                            class="text-blue-600 hover:text-blue-800 hover:underline transition-colors block w-full py-1"
                                            title="Atur Anggota Kelas ini">
                                            <?= htmlspecialchars($kls['nama_kelas']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <?= $kls['wali_kelas_ganjil'] ? htmlspecialchars($kls['wali_kelas_ganjil']) : '<span class="text-xs text-slate-400 italic">Belum diatur</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <?= $kls['wali_kelas_genap'] ? htmlspecialchars($kls['wali_kelas_genap']) : '<span class="text-xs text-slate-400 italic">Belum diatur</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-slate-200">
                                            <?= $kls['jumlah_siswa'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($kls['jumlah_siswa'] == 0): ?>
                                            <!-- Tombol Hapus hanya jika kelas kosong -->
                                            <form action="proses.php" method="POST" class="inline">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id_kelas" value="<?= $kls['id_kelas'] ?>">
                                                <button type="button"
                                                    onclick="confirmAksi(event, this.form, 'Hapus Kelas', 'Apakah Anda yakin ingin menghapus kelas <?= htmlspecialchars($kls['nama_kelas']) ?>?', 'Ya, Hapus', '#dc2626')"
                                                    class="font-medium text-red-500 bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded-lg text-xs transition-colors">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Info tidak bisa dihapus -->
                                            <span class="text-xs text-slate-400 italic"
                                                title="Kosongkan anggota kelas terlebih dahulu untuk menghapus."><i
                                                    class="fas fa-lock"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                <p class="text-xs text-slate-500"><i class="fas fa-info-circle mr-1"></i> Kelas yang masih memiliki
                    anggota siswa tidak dapat dihapus. Anda harus memindahkan siswanya terlebih dahulu melalui halaman
                    "Pindah / Atur Anggota Kelas".</p>
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

<!-- Modal Duplikat Kelas -->
<?php if (!empty($prev_ta_list)): ?>
    <div id="modalDuplikat"
        class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800">Duplikat Rombongan Belajar</h3>
                <button onclick="document.getElementById('modalDuplikat').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="proses.php" method="POST" class="p-6">
                <input type="hidden" name="aksi" value="duplikasi_kelas">

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Ajaran Sumber</label>
                    <select name="ta_sumber"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5"
                        required>
                        <?php foreach ($prev_ta_list as $pta): ?>
                            <option value="<?= htmlspecialchars($pta) ?>"><?= htmlspecialchars($pta) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">Sistem akan menyalin semua nama kelas dari
                        tahun ajaran sumber. Tingkat kelas (X, XI) akan otomatis diubah menjadi (XI, XII). Jika ada kelas
                        XII di tahun ajaran sumber, kelas tersebut <b class="text-red-500">tidak disalin</b> karena
                        diasumsikan telah lulus.</p>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('modalDuplikat').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                        <i class="fas fa-clone mr-1"></i> Mulai Duplikat
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>