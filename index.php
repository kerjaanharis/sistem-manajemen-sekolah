<?php
require_once 'config/app.php';
require_once 'config/database.php';
require_once 'config/functions.php';

$page_title = 'Dashboard PINTU KARTANEGARA';
$current_page = 'dashboard';

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/topbar.php';
?>

<div class="mb-8 flex flex-col justify-between md:flex-row md:items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard PINTU KARTANEGARA</h2>
        <p class="text-sm text-slate-500 mt-1">Sistem Informasi Terpadu SMK Kartanegara Wates.</p>
    </div>
    <div
        class="mt-4 flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 text-sm font-medium text-slate-600 md:mt-0">
        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
        <?= format_tanggal(date('Y-m-d')) ?>
    </div>
</div>

<!-- Stats Card Overview -->
<div class="grid gap-6 mb-8 sm:grid-cols-2 xl:grid-cols-4">
    <!-- Card Data Siswa -->
    <div
        class="flex items-center p-5 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
        <div
            class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full group-hover:bg-blue-100 transition-colors z-0">
        </div>
        <div
            class="p-4 mr-4 text-blue-600 bg-blue-100 rounded-xl relative z-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-user-graduate text-2xl"></i>
        </div>
        <div class="relative z-10">
            <p class="mb-1 text-sm font-semibold text-slate-500 tracking-wide uppercase">Total Siswa Aktif</p>
            <p class="text-2xl font-bold text-slate-800">1.245</p>
        </div>
    </div>

    <!-- Card Data Pegawai -->
    <div
        class="flex items-center p-5 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
        <div
            class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:bg-emerald-100 transition-colors z-0">
        </div>
        <div
            class="p-4 mr-4 text-emerald-600 bg-emerald-100 rounded-xl relative z-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-chalkboard-user text-2xl"></i>
        </div>
        <div class="relative z-10">
            <p class="mb-1 text-sm font-semibold text-slate-500 tracking-wide uppercase">Guru & Karyawan</p>
            <p class="text-2xl font-bold text-slate-800">98</p>
        </div>
    </div>

    <!-- Card Data Absensi (RFID) -->
    <div
        class="flex items-center p-5 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
        <div
            class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full group-hover:bg-amber-100 transition-colors z-0">
        </div>
        <div
            class="p-4 mr-4 text-amber-600 bg-amber-100 rounded-xl relative z-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-id-card-clip text-2xl"></i>
        </div>
        <div class="relative z-10">
            <p class="mb-1 text-sm font-semibold text-slate-500 tracking-wide uppercase">Rata Kehadiran</p>
            <p class="text-2xl font-bold text-slate-800">95%</p>
        </div>
    </div>

    <!-- Status Sistem -->
    <div
        class="flex items-center p-5 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative overflow-hidden">
        <div
            class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full group-hover:bg-purple-100 transition-colors z-0">
        </div>
        <div
            class="p-4 mr-4 text-purple-600 bg-purple-100 rounded-xl relative z-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-server text-2xl"></i>
        </div>
        <div class="relative z-10">
            <p class="mb-1 text-sm font-semibold text-slate-500 tracking-wide uppercase">Status Sistem</p>
            <div class="flex items-center mt-1">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                <p class="text-lg font-bold text-green-600">Terhubung</p>
            </div>
        </div>
    </div>
</div>

<!-- Modul Shortcuts -->
<div class="grid gap-6 mb-8 lg:grid-cols-2">
    <!-- Pintasan Utama Modul Fase 1 & 2 -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-lg text-slate-800"><i class="fas fa-layer-group text-primary mr-2"></i> Akses
                Modul Cepat</h3>
            <span
                class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full tracking-wide text-xs font-bold shadow-inner">PENGEMBANGAN</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <a href="<?= base_url('modules/siswa') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-slate-100 hover:border-primary hover:bg-slate-50/80 transition-all focus:ring-4 ring-primary/20 hover:-translate-y-1 shadow-sm group">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100 mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Data Siswa</span>
                <span class="text-[10px] text-slate-400 font-medium mt-1">Modul 1</span>
            </a>

            <a href="<?= base_url('modules/guru_karyawan') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-slate-100 hover:border-emerald-500 hover:bg-slate-50/80 transition-all focus:ring-4 ring-emerald-500/20 hover:-translate-y-1 shadow-sm group">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm border border-emerald-100 mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fas fa-chalkboard-user text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Pegawai</span>
                <span class="text-[10px] text-slate-400 font-medium mt-1">Modul 2</span>
            </a>

            <a href="<?= base_url('modules/auth') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-slate-100 hover:border-violet-500 hover:bg-slate-50/80 transition-all focus:ring-4 ring-violet-500/20 hover:-translate-y-1 shadow-sm group">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-violet-50 text-violet-600 shadow-sm border border-violet-100 mb-4 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                    <i class="fas fa-shield-halved text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">SSO & Auth</span>
                <span class="text-[10px] text-slate-400 font-medium mt-1">Modul 3</span>
            </a>

            <a href="<?= base_url('modules/absensi') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-dashed border-slate-300 hover:border-rose-500 hover:bg-slate-50/80 transition-all focus:ring-4 ring-rose-500/20 shadow-sm group opacity-70 hover:opacity-100">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-500 shadow-sm border border-slate-200 mb-4 group-hover:bg-rose-500 group-hover:text-white group-hover:border-rose-600 transition-colors">
                    <i class="fas fa-fingerprint text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Absensi RFID</span>
                <span class="text-[10px] text-rose-500 font-bold mt-1">Fase 2</span>
            </a>

            <a href="<?= base_url('modules/keuangan') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-dashed border-slate-300 hover:border-teal-500 hover:bg-slate-50/80 transition-all focus:ring-4 ring-teal-500/20 shadow-sm group opacity-70 hover:opacity-100">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-500 shadow-sm border border-slate-200 mb-4 group-hover:bg-teal-500 group-hover:text-white group-hover:border-teal-600 transition-colors">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Keuangan</span>
                <span class="text-[10px] text-teal-500 font-bold mt-1">Fase 3</span>
            </a>

            <a href="<?= base_url('modules/analisis_kinerja') ?>"
                class="flex flex-col items-center justify-center p-5 rounded-2xl border border-dashed border-slate-300 hover:border-amber-500 hover:bg-slate-50/80 transition-all focus:ring-4 ring-amber-500/20 shadow-sm group opacity-70 hover:opacity-100 relative">
                <div class="absolute top-2 right-2 text-amber-500"><i class="fas fa-sparkles text-sm"></i></div>
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-500 shadow-sm border border-slate-200 mb-4 group-hover:bg-amber-500 group-hover:text-white group-hover:border-amber-600 transition-colors">
                    <i class="fas fa-brain text-xl"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Analisis AI</span>
                <span class="text-[10px] text-amber-500 font-bold mt-1">Fase 5</span>
            </a>
        </div>
    </div>

    <!-- Info Section / Log Cepat -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center"><i
                class="fas fa-bars-staggered text-primary mr-2"></i> Log Aktivitas Terakhir</h3>
        <div
            class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
            <!-- Item 1 -->
            <div
                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-blue-100 text-blue-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10">
                    <i class="fas fa-user-plus text-sm"></i>
                </div>
                <div
                    class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between space-x-2 mb-1">
                        <div class="font-bold text-slate-700 text-sm">Sistem Inisialisasi</div>
                        <time class="font-medium text-xs text-slate-500">Baru Saja</time>
                    </div>
                    <div class="text-slate-500 text-xs mt-2">Pembuatan struktur folder dan index dasar sistem PINTU
                        selesai dijalankan.</div>
                </div>
            </div>
            <!-- Item 2 -->
            <div
                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-emerald-100 text-emerald-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10">
                    <i class="fas fa-database text-sm"></i>
                </div>
                <div
                    class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between space-x-2 mb-1">
                        <div class="font-bold text-slate-700 text-sm">Konfigurasi Database</div>
                        <time class="font-medium text-xs text-slate-500">2 Menit lalu</time>
                    </div>
                    <div class="text-slate-500 text-xs mt-2">File koneksi PDO database db_pintu_kartanegara disiapkan.
                    </div>
                </div>
            </div>
            <!-- Item 3 -->
            <div
                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-indigo-100 text-indigo-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10">
                    <i class="fas fa-cogs text-sm"></i>
                </div>
                <div
                    class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between space-x-2 mb-1">
                        <div class="font-bold text-slate-700 text-sm">Setup Environment</div>
                        <time class="font-medium text-xs text-slate-500">5 Menit lalu</time>
                    </div>
                    <div class="text-slate-500 text-xs mt-2">Setup Tailwind CSS config CDN dan UI layout template.</div>
                </div>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="<?= base_url('modules/log_sistem') ?>"
                class="text-sm font-semibold text-primary hover:text-blue-800 transition-colors">Lihat Semua History
                &rarr;</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>