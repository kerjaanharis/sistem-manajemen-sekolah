<?php
// includes/topbar.php
?>
<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col bg-slate-50 h-screen overflow-hidden">
    <!-- Topbar -->
    <header
        class="h-16 shrink-0 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-40 relative">
        <div class="flex items-center">
            <button id="mobile-menu-btn"
                class="text-slate-500 hover:text-primary hover:bg-slate-100 rounded-lg p-2 focus:outline-none mr-4 transition-all">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <!-- Search if needed -->
            <div
                class="hidden sm:flex items-center bg-slate-100 rounded-lg px-3 py-1.5 focus-within:ring-2 ring-primary/20 transition-all">
                <i class="fas fa-search text-slate-400 text-sm"></i>
                <input type="text" placeholder="Cari di menu..."
                    class="bg-transparent border-none outline-none text-sm text-slate-700 ml-2 w-48 focus:ring-0">
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 sm:space-x-6">
            <!-- Academic Term Badge (Hidden in small mobile) -->
            <div
                class="hidden sm:flex items-center bg-blue-50/80 border border-blue-100 px-3 py-1.5 rounded-full shadow-sm hover:shadow transition-shadow">
                <i class="fas fa-graduation-cap text-blue-500 mr-2 text-sm"></i>
                <span class="text-[11px] sm:text-xs font-bold text-blue-700 tracking-wide">
                    T.A. <?= htmlspecialchars(TAHUN_AJARAN) ?> <span class="mx-1 text-blue-300">|</span>
                    <?= htmlspecialchars(SEMESTER_AKTIF) ?>
                </span>
            </div>

            <!-- Notifications -->
            <button
                onclick="Swal.fire('Fitur Notifikasi', 'Pusat pemberitahuan sistem sedang dalam tahap pengembangan.', 'info')"
                class="relative text-slate-400 hover:text-primary transition-colors focus:outline-none"
                title="Notifikasi Sistem">
                <i class="far fa-bell text-lg"></i>
                <span
                    class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-white">3</span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative z-50">
                <div id="profile-menu-btn"
                    class="flex items-center space-x-3 cursor-pointer border-l pl-6 border-slate-200 group">
                    <div class="text-right hidden sm:block">
                        <p
                            class="text-sm font-semibold text-slate-700 leading-none group-hover:text-primary transition-colors">
                            <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?>
                        </p>
                        <p class="text-xs text-slate-500 mt-1 font-medium">
                            <?= htmlspecialchars($_SESSION['role_name'] ?? 'Role') ?>
                        </p>
                    </div>
                    <?php
                    // Ciptakan Avatar berdasarkan nama user
                    $avatar_name = urlencode($_SESSION['nama_lengkap'] ?? 'U');
                    ?>
                    <img src="https://ui-avatars.com/api/?name=<?= $avatar_name ?>&background=1e40af&color=fff&rounded=true"
                        alt="Profile"
                        class="h-9 w-9 rounded-full shadow-sm border-2 border-white ring-2 ring-transparent group-hover:ring-primary/20 transition-all pointer-events-none">
                    <i
                        class="fas fa-chevron-down text-xs text-slate-400 group-hover:text-primary transition-colors pointer-events-none"></i>
                </div>

                <!-- Dropdown Content -->
                <div id="profile-dropdown"
                    class="absolute right-0 top-full mt-3 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 hidden opacity-0 transition-all duration-200 transform origin-top-right scale-95 z-[100]">
                    <div class="px-4 py-3 border-b border-slate-100 mb-1 sm:hidden bg-slate-50/50 rounded-t-xl">
                        <p class="text-sm font-bold text-slate-800">
                            <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?>
                        </p>
                        <p class="text-[11px] text-slate-500 font-medium">
                            <?= htmlspecialchars($_SESSION['role_name'] ?? 'Role') ?>
                        </p>
                    </div>

                    <a href="#"
                        onclick="Swal.fire('Segera Hadir', 'Fitur Manajemen Profil Pengguna sedang dalam pengerjaan.', 'info')"
                        class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors cursor-pointer relative z-10">
                        <i class="far fa-user w-5 text-center mr-2"></i> Profil Saya
                    </a>
                    <a href="#"
                        onclick="Swal.fire('Segera Hadir', 'Fitur Ubah Password sedang dalam pengerjaan.', 'info')"
                        class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-amber-50 hover:text-amber-700 transition-colors cursor-pointer relative z-10">
                        <i class="fas fa-lock w-5 text-center mr-2"></i> Ubah Password
                    </a>

                    <div class="border-t border-slate-100 my-1"></div>

                    <a href="<?= base_url('modules/auth/logout.php') ?>"
                        class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors font-semibold cursor-pointer relative z-10">
                        <i class="fas fa-power-off w-5 text-center mr-2"></i> Keluar Sistem
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 bg-slate-50 p-6 overflow-y-auto">