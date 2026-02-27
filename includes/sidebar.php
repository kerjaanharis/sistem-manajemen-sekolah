<?php
// includes/sidebar.php
?>
<!-- Sidebar Overlay Backdrop -->
<div id="sidebar-backdrop"
    class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300 opacity-0 cursor-pointer">
</div>

<!-- Sidebar Auto-Hidden Universal -->
<aside id="main-sidebar"
    class="w-64 border-r border-slate-800 md:w-20 hover:w-64 bg-slate-900 text-white flex flex-col transition-all duration-300 z-50 shadow-xl fixed md:static inset-y-0 left-0 -translate-x-full md:translate-x-0 h-screen shrink-0 overflow-x-hidden group">

    <!-- Brand -->
    <div
        class="h-16 flex items-center border-b border-slate-800/80 bg-slate-950/80 px-6 shrink-0 relative overflow-hidden">
        <!-- Optional: subtle glow effect -->
        <div class="absolute top-0 left-1/4 w-1/2 h-full bg-blue-500/10 blur-xl rounded-full z-0"></div>
        <h1 class="text-xl font-black flex items-center whitespace-nowrap z-10">
            <div
                class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0 border border-white/10">
                <i class="fas fa-building-columns text-white text-[15px]"></i>
            </div>
            <span
                class="opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 ml-3.5 tracking-wide flex items-center drop-shadow-sm">
                <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 mr-1.5">PINTU</span>
                <span class="text-white font-semibold">KARTANEGARA</span>
            </span>
        </h1>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto py-4 scrollbar-hide">
        <ul class="space-y-1 px-3">
            <li>
                <a href="<?= base_url() ?>"
                    class="flex items-center px-3 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= empty($current_page) || $current_page == 'dashboard' ? 'bg-primary/20 text-blue-400 font-medium' : '' ?>">
                    <i class="fas fa-gauge w-8 text-center shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Dashboard
                        Utama</span>
                </a>
            </li>

            <li class="pt-5 pb-2 px-3">
                <span
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">Modul
                    Sistem</span>
                <!-- Garis separator pengganti text saat mini -->
                <div class="h-px bg-slate-800 w-full mt-2 hidden md:block group-hover:hidden"></div>
            </li>

            <!-- Menu Data Pegawai -->
            <li>
                <a href="<?= base_url('modules/guru_karyawan') ?>"
                    class="flex items-center px-3 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'guru_karyawan' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-chalkboard-user w-8 text-center text-emerald-400 shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Data
                        Guru & Karyawan</span>
                </a>
            </li>

            <!-- Menu Tugas Tambahan -->
            <li>
                <a href="<?= base_url('modules/tugas_tambahan') ?>"
                    class="flex items-center px-3 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'tugas_tambahan' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-id-badge w-8 text-center text-indigo-400 shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Tugas
                        Tambahan</span>
                </a>
            </li>

            <!-- Menu Data Siswa -->
            <li>
                <a href="<?= base_url('modules/siswa') ?>"
                    class="flex items-center px-3 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'siswa' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-users-graduate w-8 text-center text-blue-400 shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Data
                        Siswa</span>
                </a>
            </li>

            <!-- Menu Master Kelas -->
            <li>
                <a href="<?= base_url('modules/kelas') ?>"
                    class="flex items-center px-3 py-3 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'kelas' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-layer-group w-8 text-center text-yellow-500 shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Master
                        Kelas</span>
                </a>
            </li>
        </ul>

        <ul class="space-y-1 px-3 mt-6">
            <li
                class="pt-2 pb-2 px-3 border-t border-slate-800 group-hover:border-slate-800 md:border-transparent transition-colors">
                <span
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">Pengaturan</span>
                <div class="h-px bg-slate-800 w-full hidden md:block group-hover:hidden mt-2"></div>
            </li>
            <li>
                <a href="<?= base_url('modules/pengaturan') ?>"
                    class="flex items-center px-3 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'pengaturan' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-sliders w-8 text-center shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Pengaturan
                        Sistem</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('modules/database') ?>"
                    class="flex items-center px-3 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'database' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-database w-8 text-center shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Manajemen
                        Database</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('modules/log_sistem') ?>"
                    class="flex items-center px-3 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors <?= isset($current_page) && $current_page == 'log_sistem' ? 'bg-slate-800 text-white' : '' ?>">
                    <i class="fas fa-clock-rotate-left w-8 text-center shrink-0 text-lg"></i>
                    <span
                        class="ml-3 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm">Log
                        Aktivitas</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Bottom / User profile summary & Logout -->
    <div class="border-t border-slate-800 bg-slate-950/30 shrink-0 p-3">
        <a href="<?= base_url('modules/auth/logout.php') ?>"
            class="flex items-center justify-center w-full px-3 py-3 text-red-400 hover:text-white hover:bg-red-600/80 rounded-lg transition-colors border border-transparent hover:border-red-900/50">
            <i class="fas fa-power-off w-8 text-center shrink-0 text-lg"></i>
            <span
                class="ml-2 whitespace-nowrap opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-sm font-medium">Keluar
                Sistem</span>
        </a>
    </div>
</aside>