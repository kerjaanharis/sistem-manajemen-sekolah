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

$page_title = 'Data Guru & Karyawan';
$current_page = 'guru_karyawan';

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';

// Ambil Data Pegawai
try {
    $search = $_GET['search'] ?? '';
    $tipeFilter = $_GET['tipe'] ?? '';

    $query = "SELECT p.*, u.username, r.nama_role 
              FROM data_pegawai p 
              LEFT JOIN users u ON p.id_user = u.id_user 
              LEFT JOIN roles r ON u.id_role = r.id_role
              WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (p.nama_lengkap LIKE :search OR p.nip_nik LIKE :search OR p.jabatan LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!empty($tipeFilter)) {
        $query .= " AND p.tipe_pegawai = :tipe";
        $params['tipe'] = $tipeFilter;
    }

    $query .= " ORDER BY p.tipe_pegawai ASC, p.nama_lengkap ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $pegawai_list = $stmt->fetchAll();

} catch (\PDOException $e) {
    $pegawai_list = [];
    $error_msg = "Gagal mengambil data pegawai: " . $e->getMessage();
}
?>

<div class="mb-6 flex flex-col justify-between sm:flex-row sm:items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Data Guru & Karyawan</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola data induk, profil, dan informasi kepegawaian institusi.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex space-x-2">
        <a href="<?= base_url('modules/guru_karyawan/tambah.php') ?>"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow-sm hover:bg-emerald-700 transition-colors text-sm font-medium flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Pegawai
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 flex items-start shadow-sm">
        <i class="fas fa-check-circle mt-1 mr-3 text-green-500"></i>
        <p class="text-sm font-medium">
            <?= $_SESSION['success_msg'] ?>
        </p>
    </div>
    <?php unset($_SESSION['success_msg']); endif; ?>

<?php if (isset($_SESSION['error_msg']) || isset($error_msg)): ?>
    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 flex items-start shadow-sm">
        <i class="fas fa-triangle-exclamation mt-1 mr-3 text-red-500"></i>
        <p class="text-sm font-medium">
            <?= $_SESSION['error_msg'] ?? $error_msg ?>
        </p>
    </div>
    <?php unset($_SESSION['error_msg']); endif; ?>

<!-- Filter & Table Card -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Filter Bar -->
    <div
        class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari NIP / Nama..."
                    class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 w-full sm:w-64 bg-white">
            </div>

            <select name="tipe"
                class="px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white">
                <option value="">Semua Tipe</option>
                <option value="Guru" <?= $tipeFilter == 'Guru' ? 'selected' : '' ?>>Guru</option>
                <option value="Karyawan" <?= $tipeFilter == 'Karyawan' ? 'selected' : '' ?>>Karyawan / TU</option>
            </select>

            <button type="submit"
                class="px-3 py-2 bg-slate-100 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors text-sm font-medium">Filter</button>
            <?php if (!empty($search) || !empty($tipeFilter)): ?>
                <a href="index.php"
                    class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium text-center">Reset</a>
            <?php endif; ?>
        </form>

        <div class="text-sm text-slate-500 font-medium whitespace-nowrap">
            Total: <span class="text-slate-800">
                <?= count($pegawai_list) ?> Pegawai
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr
                    class="bg-white border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-4 w-10 text-center">No</th>
                    <th class="px-6 py-4">Profil Pegawai</th>
                    <th class="px-6 py-4">NIP / NIK</th>
                    <th class="px-6 py-4">Jabatan</th>
                    <th class="px-6 py-4">Status & Akun</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <?php if (empty($pegawai_list)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <div
                                class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user-xmark text-2xl"></i>
                            </div>
                            <p class="font-medium text-slate-600">Tidak ada data pegawai ditemukan.</p>
                            <p class="text-xs mt-1">Silakan tambah data baru atau sesuaikan filter pencarian.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1;
                    foreach ($pegawai_list as $row): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-medium">
                                <?= $no++ ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['nama_lengkap']) ?>&background=10b981&color=fff&rounded=true"
                                        alt="Avatar" class="h-10 w-10 rounded-full border-2 border-white shadow-sm mr-3">
                                    <div>
                                        <h4 class="font-bold text-slate-800">
                                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                                        </h4>
                                        <span class="text-[11px] text-slate-500 flex items-center mt-0.5">
                                            <span
                                                class="px-1.5 py-0.5 rounded <?= $row['tipe_pegawai'] == 'Guru' ? 'bg-indigo-50 text-indigo-600' : 'bg-orange-50 text-orange-600' ?> border <?= $row['tipe_pegawai'] == 'Guru' ? 'border-indigo-100' : 'border-orange-100' ?>">
                                                <?= htmlspecialchars($row['tipe_pegawai']) ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">
                                    <?= htmlspecialchars($row['nip_nik']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-600 text-sm font-medium">
                                    <?= htmlspecialchars($row['jabatan'] ?: '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5 items-start">
                                    <?php if ($row['status_pegawai'] == 'Aktif'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-circle text-[8px] mr-1.5 text-emerald-500"></i> AKTIF
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <i class="fas fa-circle text-[8px] mr-1.5 text-slate-400"></i>
                                            <?= strtoupper($row['status_pegawai']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($row['id_user']): ?>
                                        <span
                                            class="text-[11px] text-indigo-600 font-medium flex items-center bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100/50 text-left"
                                            title="Role: <?= htmlspecialchars($row['nama_role']) ?>">
                                            <i class="fas fa-shield-halved text-[10px] mr-1"></i> <span
                                                class="max-w-[70px] truncate">
                                                <?= htmlspecialchars($row['username']) ?>
                                            </span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[11px] text-rose-500 font-medium flex items-center"
                                            title="Belum memiliki akun SSO">
                                            <i class="fas fa-link-slash text-[10px] mr-1"></i> No Akun
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div
                                    class="flex items-center justify-center space-x-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                    <a href="<?= base_url('modules/guru_karyawan/edit.php?id=' . $row['id_pegawai']) ?>"
                                        class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors border border-amber-200 hover:border-amber-500"
                                        title="Edit Pegawai">
                                        <i class="fas fa-pen text-sm"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete(<?= $row['id_pegawai'] ?>, '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors border border-red-200 hover:border-red-500"
                                        title="Hapus Pegawai">
                                        <i class="fas fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            html: `Menghapus data pegawai <b>${name}</b>.<br>Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            confirmButtonText: '<i class="fas fa-trash-can mr-2"></i>Ya, Hapus Data',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'shadow-sm',
                cancelButton: 'shadow-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= base_url('modules/guru_karyawan/proses.php') ?>?action=delete&id=${id}`;
            }
        });
    }
</script>

<?php require_once '../../includes/footer.php'; ?>