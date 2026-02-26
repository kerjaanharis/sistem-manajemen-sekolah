<?php
// includes/footer.php
// Format Tanggal Indonesia
$hari_array = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan_array = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$hari = $hari_array[(int) date('w')];
$tanggal = date('j');
$bulan = $bulan_array[(int) date('n')];
$tahun = date('Y');
$tanggal_indo = "$hari, $tanggal $bulan $tahun";

// Resource Server (Memory Allocated for PHP)
$mem_usage = round(memory_get_usage(true) / 1024 / 1024, 2);

// Durasi Login
$login_time = isset($_SESSION['login_time']) ? $_SESSION['login_time'] : time();
$duration = time() - $login_time;
$hours = floor($duration / 3600);
$minutes = floor(($duration % 3600) / 60);
$duration_str = ($hours > 0 ? $hours . 'j ' : '') . $minutes . 'm';
if ($hours == 0 && $minutes == 0)
    $duration_str = $duration . 'd';
?>
</main>
<!-- Footer Info Sistem (Sticky Bottom) -->
<footer
    class="bg-white border-t border-slate-200 py-3 px-6 flex flex-col md:flex-row items-center justify-between z-20 shrink-0">
    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto mb-3 md:mb-0 justify-center md:justify-start">
        <span
            class="flex items-center bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-100">
            <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>
            <?= $tanggal_indo ?>
        </span>
        <span
            class="flex items-center bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-100">
            <i class="far fa-clock mr-2 text-indigo-500"></i>
            <span id="realtime-clock" class="tracking-widest"><?= date('H:i:s') ?> WIB</span>
        </span>
    </div>
    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-center md:justify-end">
        <span
            class="flex items-center bg-emerald-50/50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-medium border border-emerald-100/50"
            title="Alokasi RAM PHP Aktif">
            <i class="fas fa-server mr-2 text-emerald-500"></i> RAM Server: <span class="ml-1"><?= $mem_usage ?>
                MB</span>
        </span>
        <span
            class="flex items-center bg-indigo-50/50 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-medium border border-indigo-100/50"
            title="Waktu sejak login">
            <i class="fas fa-history mr-2 text-indigo-500"></i> Durasi Login:
            <span id="login-duration-counter" data-start="<?= $login_time ?>"
                class="ml-1 tracking-widest"><?= $duration_str ?></span>
        </span>
    </div>
</footer>
</div><!-- End Main Content Wrapper -->
</div><!-- End Flex Container h-screen -->

<!-- Script Global -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('main-sidebar');
        const menuBtn = document.getElementById('mobile-menu-btn');
        const backdrop = document.getElementById('sidebar-backdrop');
        let isSidebarOpen = false;

        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            if (isSidebarOpen) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden', 'opacity-0');
                // Allow transition to finish before fully showing backdrop
                setTimeout(() => backdrop.classList.add('opacity-100'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                setTimeout(() => backdrop.classList.add('hidden'), 300);
            }
        }

        if (menuBtn) menuBtn.addEventListener('click', toggleSidebar);
        if (backdrop) backdrop.addEventListener('click', toggleSidebar);

        // Profile Dropdown Toggle Logic
        const profileBtn = document.getElementById('profile-menu-btn');
        const profileDropdown = document.getElementById('profile-dropdown');

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation(); // Mencegah klik menyebar ke window
                const isHidden = profileDropdown.classList.contains('hidden');

                if (isHidden) {
                    profileDropdown.classList.remove('hidden');
                    // Timeout untuk efek transisi CSS
                    setTimeout(() => {
                        profileDropdown.classList.remove('opacity-0', 'scale-95');
                        profileDropdown.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else {
                    profileDropdown.classList.remove('opacity-100', 'scale-100');
                    profileDropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => profileDropdown.classList.add('hidden'), 200);
                }
            });

            // Tutup dropdown jika user mengklik bagian luar elemen
            window.addEventListener('click', (e) => {
                if (!profileDropdown.classList.contains('hidden') && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.remove('opacity-100', 'scale-100');
                    profileDropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => profileDropdown.classList.add('hidden'), 200);
                }
            });
        }

        // Update Realtime Clock & Login Duration
        function updateSystemInfo() {
            // Clock
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false }).replace(/\./g, ':') + ' WIB';
            const clockEl = document.getElementById('realtime-clock');
            if (clockEl) clockEl.textContent = timeString;

            // Login Duration
            const durationEl = document.getElementById('login-duration-counter');
            if (durationEl) {
                const startTimestamp = parseInt(durationEl.getAttribute('data-start'), 10);
                const currentTimestamp = Math.floor(Date.now() / 1000);
                const diff = currentTimestamp - startTimestamp;

                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;

                let durationStr = '';
                if (hours > 0) durationStr += hours + 'j ';

                if (hours > 0 || minutes > 0) {
                    durationStr += minutes + 'm';
                } else {
                    durationStr = seconds + 'd'; // Jika masih dibawah 1 menit, tampilkan detik
                }

                if (durationEl.textContent !== durationStr) {
                    durationEl.textContent = durationStr;
                }
            }
        }

        setInterval(updateSystemInfo, 1000);
    });
</script>
</body>

</html>