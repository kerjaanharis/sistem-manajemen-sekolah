<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role_name']), ['administrator', 'kepala sekolah'])) {
    $_SESSION['error_msg'] = "Akses ditolak.";
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = 'Atur Anggota Kelas';
$current_page = 'kelas';

// Get list of active classes for this TA
$stmt_kelas = $pdo->prepare("SELECT id_kelas, nama_kelas, tingkat FROM master_kelas WHERE tahun_ajaran = ? ORDER BY tingkat ASC, nama_kelas ASC");
$stmt_kelas->execute([TAHUN_AJARAN]);
$kelas_list = $stmt_kelas->fetchAll();

// Get students NOT YET assigned to ANY class in this TA (and their status is 'Aktif')
$stmt_unassigned = $pdo->prepare("
    SELECT id_siswa, nis, nama_lengkap, jenis_kelamin
    FROM data_siswa 
    WHERE status_siswa = 'Aktif' 
      AND id_siswa NOT IN (
          SELECT a.id_siswa 
          FROM anggota_kelas a 
          JOIN master_kelas k ON a.id_kelas = k.id_kelas 
          WHERE k.tahun_ajaran = ?
      )
    ORDER BY nama_lengkap ASC
");
$stmt_unassigned->execute([TAHUN_AJARAN]);
$unassigned_students = $stmt_unassigned->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/topbar.php';
?>

<!-- Load SortableJS for Drag and Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Atur Anggota Kelas</h2>
        <p class="text-sm text-slate-500 mt-1">Geser (Drag & Drop) nama siswa untuk memindahkannya antar kelas.</p>
    </div>
    <div class="mt-4 md:mt-0">
        <a href="index.php"
            class="text-slate-600 bg-slate-100 hover:bg-slate-200 focus:ring-4 focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex items-center shadow-sm transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Master Kelas
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri: Daftar Siswa Belum Punya Kelas (Siswa Baru / Naik Kelas) -->
    <div class="lg:col-span-1 border-r border-slate-100 pr-0 lg:pr-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-[80vh]">
            <div
                class="border-b border-slate-100 bg-amber-50 rounded-t-2xl p-4 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-slate-800 flex items-center">
                    <i class="fas fa-users-slash text-amber-500 mr-2"></i> Siswa Belum Diatur
                </h3>
                <span
                    class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-full border border-amber-200">
                    <span id="jml-unassigned">
                        <?= count($unassigned_students) ?>
                    </span> Siswa
                </span>
            </div>
            <div class="p-3 bg-slate-50 border-b border-slate-100 shrink-0">
                <input type="text" id="search-unassigned" placeholder="Cari nama siswa..."
                    class="bg-white border text-xs border-slate-300 text-slate-900 rounded-lg focus:ring-primary focus:border-primary block w-full p-2">
            </div>

            <!-- Container Draggable (Siswa Belum Ada Kelas) -->
            <ul id="list-unassigned" class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50/50 sortable-list"
                data-kelas="0">
                <?php foreach ($unassigned_students as $s): ?>
                    <li data-id="<?= $s['id_siswa'] ?>"
                        class="flex items-center p-3 text-sm bg-white border border-slate-200 rounded-lg shadow-sm cursor-grab hover:bg-slate-50 hover:border-blue-300 transition-colors">
                        <i class="fas fa-grip-vertical text-slate-400 mr-3 cursor-grab"></i>
                        <div class="flex-1">
                            <span class="font-medium text-slate-800 block">
                                <?= htmlspecialchars($s['nama_lengkap']) ?>
                            </span>
                            <span class="text-[10px] text-slate-500">NIS:
                                <?= htmlspecialchars($s['nis']) ?>
                            </span>
                        </div>
                        <?php if ($s['jenis_kelamin'] == 'L'): ?>
                            <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded ml-2">L</span>
                        <?php else: ?>
                            <span class="text-[10px] font-bold text-pink-500 bg-pink-50 px-2 py-0.5 rounded ml-2">P</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($unassigned_students)): ?>
                    <div id="empty-unassigned" class="text-center p-4 text-xs text-slate-400 italic">Semua siswa aktif sudah
                        masuk ke dalam kelas.</div>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Kolom Kanan: Daftar Kelas yang Ada -->
    <div class="lg:col-span-2 flex flex-col h-[80vh]">

        <!-- Filter Section -->
        <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 mb-4 flex gap-3 shrink-0">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="filter-nama-kelas"
                        placeholder="Cari nama kelas atau jurusan (ex: TKJ, RPL)..."
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full ps-10 p-2.5">
                </div>
            </div>
            <div class="w-1/3">
                <select id="filter-tingkat"
                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                    <option value="">Semua Tingkat / Jenjang</option>
                    <?php
                    $unique_tingkat = array_unique(array_column($kelas_list, 'tingkat'));
                    sort($unique_tingkat);
                    foreach ($unique_tingkat as $tkg):
                        ?>
                        <option value="<?= htmlspecialchars($tkg) ?>">Tingkat <?= htmlspecialchars($tkg) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 overflow-y-auto pr-2 scrollbar-hide" id="kelas-grid">

            <?php foreach ($kelas_list as $kls):
                // Get students for this class
                $stmt_anggota = $pdo->prepare("
                    SELECT s.id_siswa, s.nis, s.nama_lengkap, s.jenis_kelamin 
                    FROM anggota_kelas a
                    JOIN data_siswa s ON a.id_siswa = s.id_siswa
                    WHERE a.id_kelas = ? AND s.status_siswa = 'Aktif'
                    ORDER BY s.nama_lengkap ASC
                ");
                $stmt_anggota->execute([$kls['id_kelas']]);
                $anggota = $stmt_anggota->fetchAll();
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-96 kelas-box"
                    data-tingkat="<?= htmlspecialchars(strtoupper(trim($kls['tingkat']))) ?>"
                    data-namakelas="<?= htmlspecialchars(strtolower(trim($kls['nama_kelas']))) ?>">
                    <!-- Header Kelas -->
                    <div
                        class="border-b border-slate-100 bg-blue-50/50 rounded-t-xl p-3 flex justify-between items-center shrink-0">
                        <h3 class="font-bold text-slate-800 text-sm flex items-center">
                            <i class="fas fa-chalkboard text-blue-500 mr-2"></i>
                            <?= htmlspecialchars($kls['nama_kelas']) ?>
                        </h3>
                        <div class="flex items-center gap-2">
                            <span
                                class="bg-blue-100 text-blue-800 text-[10px] font-semibold px-2 py-0.5 rounded border border-blue-200">
                                <span id="jml-kls-<?= $kls['id_kelas'] ?>">
                                    <?= count($anggota) ?>
                                </span> Siswa
                            </span>
                        </div>
                    </div>

                    <!-- Luluskan Button khusus utk Kelas XII -->
                    <?php if (strtoupper($kls['tingkat']) == 'XII'): ?>
                        <div class="bg-emerald-50 border-b border-emerald-100 p-2 shrink-0 flex justify-between items-center">
                            <span class="text-[10px] text-emerald-700 font-medium">Tingkat Akhir</span>
                            <form action="proses.php" method="POST" class="inline m-0 p-0">
                                <input type="hidden" name="aksi" value="luluskan_semua">
                                <input type="hidden" name="id_kelas" value="<?= $kls['id_kelas'] ?>">
                                <button type="button"
                                    onclick="confirmAksi(event, this.form, 'Luluskan Kelas <?= $kls['nama_kelas'] ?>?', 'Semua siswa di kelas ini akan diluluskan pada Tahun Lulus <?= TAHUN_AJARAN ?> dan akan hilang dari daftar aktif. Lanjutkan?', 'Ya, Luluskan Semua', '#10b981')"
                                    class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1 px-3 rounded shadow-sm transition-colors">
                                    <i class="fas fa-graduation-cap"></i> Luluskan
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Container Draggable Kelas -->
                    <ul id="list-kls-<?= $kls['id_kelas'] ?>"
                        class="flex-1 overflow-y-auto p-3 space-y-2 bg-slate-50/30 sortable-list min-h-[50px]"
                        data-kelas="<?= $kls['id_kelas'] ?>">
                        <?php foreach ($anggota as $s): ?>
                            <li data-id="<?= $s['id_siswa'] ?>"
                                class="flex items-center p-2 text-xs bg-white border border-slate-200 rounded shadow-sm cursor-grab hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                <i class="fas fa-grip-vertical text-slate-300 mr-2"></i>
                                <div class="flex-1 truncate">
                                    <span class="font-medium text-slate-800 truncate">
                                        <?= htmlspecialchars($s['nama_lengkap']) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- FILTERING LOGIC ---
        const filterNama = document.getElementById('filter-nama-kelas');
        const filterTingkat = document.getElementById('filter-tingkat');
        const kelasBoxes = document.querySelectorAll('.kelas-box');

        function applyFilters() {
            const namaVal = filterNama.value.toLowerCase().trim();
            const tingkatVal = filterTingkat.value.toUpperCase();

            kelasBoxes.forEach(box => {
                const boxNama = box.getAttribute('data-namakelas');
                const boxTingkat = box.getAttribute('data-tingkat');

                const matchesNama = boxNama.includes(namaVal);
                const matchesTingkat = (tingkatVal === "") || (boxTingkat === tingkatVal);

                if (matchesNama && matchesTingkat) {
                    box.style.display = 'flex'; // Box structure uses flex-col
                } else {
                    box.style.display = 'none';
                }
            });
        }

        filterNama.addEventListener('input', applyFilters);
        filterTingkat.addEventListener('change', applyFilters);
        // --- END FILTERING LOGIC ---
        // Init SortableJS on all lists with class 'sortable-list'
        const sortables = document.querySelectorAll('.sortable-list');

        sortables.forEach(el => {
            new Sortable(el, {
                group: 'shared', // set both lists to same group
                animation: 150,
                ghostClass: 'bg-blue-100', // Class saat di drag

                onEnd: function (evt) {
                    var itemEl = evt.item;  // dragged element
                    var id_siswa = itemEl.getAttribute('data-id');
                    var toList = evt.to;    // target list
                    var id_kelas_baru = toList.getAttribute('data-kelas');

                    if (evt.from === evt.to) {
                        return; // Tidak pindah kelas
                    }

                    // Update UI Counters
                    updateCounters();

                    // Lakukan via AJAX
                    savePindahKelas(id_siswa, id_kelas_baru, itemEl, evt.from);
                },
            });
        });

        // Search filter for unassigned students
        document.getElementById('search-unassigned').addEventListener('keyup', function (e) {
            let term = e.target.value.toLowerCase();
            let items = document.querySelectorAll('#list-unassigned li');
            items.forEach(li => {
                let text = li.textContent.toLowerCase();
                li.style.display = text.includes(term) ? '' : 'none';
            });
        });
    });

    function updateCounters() {
        // Count unassigned
        const unassignedList = document.getElementById('list-unassigned');
        const unCount = unassignedList.querySelectorAll('li').length;
        document.getElementById('jml-unassigned').textContent = unCount;

        if (unCount === 0 && !document.getElementById('empty-unassigned')) {
            // Opsional tangani tampilan empty state
        } else if (unCount > 0 && document.getElementById('empty-unassigned')) {
            document.getElementById('empty-unassigned').remove();
        }

        // Count class items
        <?php foreach ($kelas_list as $kls): ?>
            var klsList_<?= $kls['id_kelas'] ?> = document.getElementById('list-kls-<?= $kls['id_kelas'] ?>');
            if (klsList_ <?= $kls['id_kelas'] ?>) {
                var cnt = klsList_ <?= $kls['id_kelas'] ?>.querySelectorAll('li').length;
                document.getElementById('jml-kls-<?= $kls['id_kelas'] ?>').textContent = cnt;
            }
        <?php endforeach; ?>
    }

    function savePindahKelas(id_siswa, id_kelas_baru, elem, fromList) {
        // Visual cue loading (optional)
        elem.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('aksi', 'pindah_siswa');
        formData.append('id_siswa', id_siswa);
        formData.append('id_kelas_baru', id_kelas_baru);

        fetch('proses.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                elem.style.opacity = '1';
                if (data.status !== 'success') {
                    Swal.fire('Error', data.message || 'Gagal memindahkan siswa', 'error');
                    // Revert
                    fromList.appendChild(elem);
                    updateCounters();
                }
            })
            .catch(error => {
                elem.style.opacity = '1';
                Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
                // Revert
                fromList.appendChild(elem);
                updateCounters();
            });
    }

    // Confirmation for Graduation
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