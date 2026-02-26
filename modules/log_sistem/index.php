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

$page_title = 'Log Aktivitas Sistem';
$current_page = 'dashboard';

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Filter setup
$filter_nama = sanitize($_GET['nama'] ?? '');
$filter_role = sanitize($_GET['role'] ?? '');
$filter_aktifitas = sanitize($_GET['aktifitas'] ?? '');
$filter_waktu_mulai = sanitize($_GET['waktu_mulai'] ?? '');
$filter_waktu_akhir = sanitize($_GET['waktu_akhir'] ?? '');

$where_clauses = ["1=1"];
$params = [];

if (!empty($filter_nama)) {
    $where_clauses[] = "nama_user LIKE :nama";
    $params['nama'] = "%$filter_nama%";
}
if (!empty($filter_role)) {
    $where_clauses[] = "peran = :role";
    $params['role'] = $filter_role;
}
if (!empty($filter_aktifitas)) {
    $where_clauses[] = "aktifitas = :aktifitas";
    $params['aktifitas'] = $filter_aktifitas;
}
if (!empty($filter_waktu_mulai)) {
    $where_clauses[] = "DATE(waktu) >= :waktu_mulai";
    $params['waktu_mulai'] = $filter_waktu_mulai;
}
if (!empty($filter_waktu_akhir)) {
    $where_clauses[] = "DATE(waktu) <= :waktu_akhir";
    $params['waktu_akhir'] = $filter_waktu_akhir;
}

$where_sql = implode(" AND ", $where_clauses);

// Get Total Rows
try {
    $stmt_count = $pdo->prepare("SELECT COUNT(id_log) as total FROM log_aktivitas WHERE $where_sql");
    $stmt_count->execute($params);
    $total_rows = $stmt_count->fetch()['total'];
    $total_pages = ceil($total_rows / $limit);

    // Get Data
    $query = "SELECT id_log, peran, nama_user, aktifitas, keterangan_tambahan, ip_address, user_agent, waktu 
              FROM log_aktivitas 
              WHERE $where_sql 
              ORDER BY waktu DESC 
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);

    // Bind parameters
    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $logs = $stmt->fetchAll();

    // Get unique roles and activities for dropdown filter
    $roles_stmt = $pdo->query("SELECT DISTINCT peran FROM log_aktivitas WHERE peran IS NOT NULL");
    $available_roles = $roles_stmt->fetchAll(PDO::FETCH_COLUMN);

    $act_stmt = $pdo->query("SELECT DISTINCT aktifitas FROM log_aktivitas WHERE aktifitas IS NOT NULL");
    $available_activities = $act_stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (\PDOException $e) {
    $logs = [];
    $total_rows = 0;
    $total_pages = 1;
    $available_roles = [];
    $available_activities = [];
    $error_db = "Peringatan: " . $e->getMessage();
}
?>

<div class="mb-6 flex flex-col justify-between sm:flex-row sm:items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Sistem Log Aktivitas Terpadu</h2>
        <p class="text-sm text-slate-500 mt-1">Pantau seluruh rekam jejak sistem secara mendetail.</p>
    </div>
</div>

<?php if (isset($error_db)): ?>
    <div class="mb-6 bg-yellow-50 text-yellow-700 p-4 rounded-xl border border-yellow-200 flex items-start shadow-sm">
        <i class="fas fa-triangle-exclamation mt-1 mr-3"></i>
        <p class="text-sm font-medium"><?= $error_db ?></p>
    </div>
<?php endif; ?>

<!-- Filter Box -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Nama
                Pengguna</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($filter_nama) ?>" placeholder="Cari nama..."
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Role Akses</label>
            <select name="role"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <option value="">Semua Role</option>
                <?php foreach ($available_roles as $r): ?>
                    <option value="<?= htmlspecialchars($r) ?>" <?= $filter_role == $r ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Aktivitas</label>
            <select name="aktifitas"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <option value="">Semua Aktivitas</option>
                <?php foreach ($available_activities as $act): ?>
                    <option value="<?= htmlspecialchars($act) ?>" <?= $filter_aktifitas == $act ? 'selected' : '' ?>>
                        <?= htmlspecialchars($act) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Waktu (Rentang
                Tanggal)</label>
            <div class="flex items-center space-x-2">
                <input type="date" name="waktu_mulai" value="<?= htmlspecialchars($filter_waktu_mulai) ?>"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <span class="text-slate-400">-</span>
                <input type="date" name="waktu_akhir" value="<?= htmlspecialchars($filter_waktu_akhir) ?>"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
        </div>
        <div class="flex space-x-2">
            <button type="submit"
                class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium text-sm flex items-center justify-center">
                <i class="fas fa-filter text-xs mr-2"></i> Terapkan
            </button>
            <a href="index.php"
                class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition font-medium text-sm flex items-center justify-center">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Tabel Log -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
                <tr
                    class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="px-5 py-4 w-16 text-center">ID Log</th>
                    <th class="px-5 py-4 w-40">Waktu</th>
                    <th class="px-5 py-4">Pengguna & Role</th>
                    <th class="px-5 py-4">Aktivitas</th>
                    <th class="px-5 py-4">Jaringan & Klien</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <?php if (empty($logs) && !isset($error_db)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <i class="fas fa-folder-open text-4xl mb-3 text-slate-200"></i>
                            <p class="font-medium text-slate-600">Tidak ada log aktivitas sesuai filter.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log):
                        // Badge color mapping
                        $act = strtolower($log['aktifitas'] ?? '');
                        $badge_class = 'bg-slate-100 text-slate-700';
                        if (strpos($act, 'login') !== false && strpos($act, 'gagal') === false)
                            $badge_class = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                        elseif (strpos($act, 'gagal') !== false)
                            $badge_class = 'bg-rose-100 text-rose-800 border border-rose-200';
                        elseif (strpos($act, 'logout') !== false)
                            $badge_class = 'bg-slate-200 text-slate-800';
                        elseif (strpos($act, 'tambah') !== false)
                            $badge_class = 'bg-blue-100 text-blue-800 border border-blue-200';
                        elseif (strpos($act, 'edit') !== false)
                            $badge_class = 'bg-amber-100 text-amber-800 border border-amber-200';
                        elseif (strpos($act, 'hapus') !== false)
                            $badge_class = 'bg-red-100 text-red-800 border border-red-200';
                        elseif (strpos($act, 'backup') !== false || strpos($act, 'restore') !== false)
                            $badge_class = 'bg-purple-100 text-purple-800 border border-purple-200';
                        ?>
                        <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer" onclick="openLogModal('<?= htmlspecialchars(addslashes($log['aktifitas'] ?? 'Detail')) ?>', '<?= htmlspecialchars(addslashes(str_replace(PHP_EOL, '<br>', $log['keterangan_tambahan'] ?? ''))) ?>', '<?= date('d M Y - H:i:s', strtotime($log['waktu'])) ?>', '<?= htmlspecialchars(addslashes($log['nama_user'] ?? '')) ?>')">
                            <td class="px-5 py-4 text-center text-slate-400 font-mono text-xs">
                                #<?= str_pad($log['id_log'], 5, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                <div class="font-semibold text-slate-700"><?= date('d M Y', strtotime($log['waktu'])) ?></div>
                                <div class="text-[11px] font-mono text-slate-500"><i class="far fa-clock mr-1"></i>
                                    <?= date('H:i:s', strtotime($log['waktu'])) ?> WIB</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-xs font-bold mr-3">
                                        <?= substr($log['nama_user'] ?? '?', 0, 1) ?>
                                    </div>
                                    <div>
                                        <span
                                            class="block text-slate-800 font-bold"><?= htmlspecialchars($log['nama_user'] ?? 'Sistem') ?></span>
                                        <span
                                            class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] uppercase font-bold tracking-wider">
                                            <?= htmlspecialchars($log['peran'] ?? 'Guest') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex px-2.5 py-1 text-[11px] font-bold rounded-md uppercase tracking-wide <?= $badge_class ?>">
                                    <?= htmlspecialchars($log['aktifitas'] ?? 'Misterius') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-[11px] text-slate-600 font-mono leading-relaxed space-y-1">
                                    <div class="flex items-center"><i class="fas fa-network-wired w-4 text-emerald-500"></i>
                                        <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></div>
                                    <div class="flex items-center truncate max-w-[150px]"
                                        title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>"><i
                                            class="fas fa-laptop text-slate-400 w-4"></i>
                                        <?= htmlspecialchars(substr($log['user_agent'] ?? 'Unknown', 0, 20)) ?>...</div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
        <div class="p-4 border-t border-slate-100 bg-white flex items-center justify-between">
            <span class="text-sm text-slate-500">
                Menampilkan <span class="font-medium text-slate-700"><?= count($logs) ?></span> dari <span
                    class="font-medium text-slate-700"><?= $total_rows ?></span> baris sejarah
            </span>
            <div class="flex items-center space-x-1">
                <?php
                $query_string = $_GET;

                // Prev button
                if ($page > 1) {
                    $query_string['page'] = $page - 1;
                    echo '<a href="?' . http_build_query($query_string) . '" class="px-3 py-1.5 bg-white border border-slate-200 rounded text-slate-600 hover:bg-slate-50 hover:text-emerald-600 transition text-sm font-medium"><i class="fas fa-chevron-left text-[10px] mr-1"></i> Sebelumnya</a>';
                } else {
                    echo '<button disabled class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded text-slate-400 cursor-not-allowed text-sm font-medium"><i class="fas fa-chevron-left text-[10px] mr-1"></i> Sebelumnya</button>';
                }

                // Page numbers (simplified version)
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                for ($i = $start_page; $i <= $end_page; $i++) {
                    $query_string['page'] = $i;
                    if ($i == $page) {
                        echo '<button disabled class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm font-bold shadow-sm">' . $i . '</button>';
                    } else {
                        echo '<a href="?' . http_build_query($query_string) . '" class="px-3 py-1.5 bg-white border border-slate-200 rounded text-slate-600 hover:bg-slate-50 hover:text-emerald-600 transition text-sm font-medium">' . $i . '</a>';
                    }
                }

                // Next button
                if ($page < $total_pages) {
                    $query_string['page'] = $page + 1;
                    echo '<a href="?' . http_build_query($query_string) . '" class="px-3 py-1.5 bg-white border border-slate-200 rounded text-slate-600 hover:bg-slate-50 hover:text-emerald-600 transition text-sm font-medium">Berikutnya <i class="fas fa-chevron-right text-[10px] ml-1"></i></a>';
                } else {
                    echo '<button disabled class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded text-slate-400 cursor-not-allowed text-sm font-medium">Berikutnya <i class="fas fa-chevron-right text-[10px] ml-1"></i></button>';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Pop-up Log Detail -->
<div id="logModal"
    class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300"
        id="logModalContent">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center">
                <i class="fas fa-scroll text-yellow-400 mr-2"></i> Detail Eksekusi
            </h3>
            <button onclick="closeLogModal()" class="text-slate-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6">
            <div class="mb-4">
                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Aktivitas Trigger</div>
                <div class="font-bold text-lg text-emerald-600" id="modalAktivitas">Login</div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-5 text-sm h-64 overflow-y-auto">
                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2">Keterangan Rinci /
                    Payload</div>
                <div class="text-slate-700 leading-relaxed font-mono text-[13px]" id="modalKeterangan">
                    Keterangan detail akan muncul di sini...
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <div><i class="far fa-user text-slate-400 mr-1"></i> Eksekutor: <span id="modalActor"
                        class="text-slate-700">Admin</span></div>
                <div><i class="far fa-clock text-slate-400 mr-1"></i> Waktu: <span id="modalWaktu">23 Jan 2024</span>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 text-right">
            <button onclick="closeLogModal()"
                class="px-5 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition shadow-sm">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<script>
    // Modal Scripts
    const logModal = document.getElementById('logModal');
    const modalContent = document.getElementById('logModalContent');

    function openLogModal(aktivitas, keterangan, waktu, actor) {
        document.getElementById('modalAktivitas').innerText = aktivitas;
        document.getElementById('modalKeterangan').innerHTML = keterangan;
        document.getElementById('modalWaktu').innerText = waktu;
        document.getElementById('modalActor').innerText = actor;

        logModal.classList.remove('hidden');
        // Sedikit delay agar transisi animasi opacity dan scale bisa terhajar oleh CSS
        setTimeout(() => {
            logModal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10);
    }

    function closeLogModal() {
        logModal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            logModal.classList.add('hidden');
        }, 300); // 300ms sesuaikan durasi transition tailwind di atas
    }

    // Menutup modal jika klik di luar area konten
    logModal.addEventListener('click', function (e) {
        if (e.target === logModal) {
            closeLogModal();
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>