<?php
session_start();
require_once '../../config/app.php';
require_once '../../config/functions.php';

// Jika sudah login, redirect ke dashboard utama
if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login -
        <?= APP_NAME ?>
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1e40af', // Blue 800
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Decoration -->
    <div
        class="absolute top-0 -left-1/4 w-1/2 h-1/2 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob">
    </div>
    <div
        class="absolute top-0 -right-1/4 w-1/2 h-1/2 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000">
    </div>
    <div
        class="absolute -bottom-32 left-1/4 w-1/2 h-1/2 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000">
    </div>

    <div class="container mx-auto px-4 z-10 relative">
        <div
            class="max-w-4xl mx-auto flex flex-col md:flex-row bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/50">
            <!-- Left Side - Branding -->
            <div
                class="md:w-5/12 bg-gradient-to-br from-blue-700 to-indigo-900 p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10">
                </div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-bold mb-2 tracking-tight">PINTU KARTANEGARA</h1>
                    <p class="text-blue-100 text-sm font-medium">Pusat Informasi Terpadu Utama</p>
                </div>

                <div class="relative z-10 my-10 flex-1 flex flex-col justify-center">
                    <div
                        class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mb-6 backdrop-blur-sm border border-white/20">
                        <i class="fas fa-building-columns text-4xl text-blue-200"></i>
                    </div>
                    <h2 class="text-2xl font-semibold mb-4 text-white">Sistem Terpusat SMK</h2>
                    <p class="text-blue-100/80 text-sm leading-relaxed">Kelola semua aktivitas akademik, absensi,
                        keuangan, hingga administrasi sekolah dalam satu platform cerdas berasitektur modular.</p>
                </div>

                <div class="relative z-10 hidden md:block border-t border-white/20 pt-6">
                    <p class="text-xs text-blue-200/80 text-center">&copy;
                        <?= date('Y') ?> Developer PINTU.
                    </p>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="md:w-7/12 p-10 lg:p-14">
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-slate-800 font-display">Selamat Datang</h2>
                    <p class="text-slate-500 mt-2">Silakan masuk menggunakan kredensial SSO Anda.</p>
                </div>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="mb-6 bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 flex items-start">
                        <i class="fas fa-circle-exclamation mt-1 mr-3"></i>
                        <p class="text-sm font-medium">
                            <?= $_SESSION['error_msg'] ?>
                        </p>
                    </div>
                    <?php unset($_SESSION['error_msg']); endif; ?>

                <form action="process_login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username / NIS /
                            NIP</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="far fa-user text-slate-400"></i>
                            </div>
                            <input type="text" id="username" name="username" required
                                class="pl-11 w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all pb-3"
                                placeholder="Masukkan username Anda">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                            <a href="#"
                                class="text-sm text-primary hover:text-blue-800 font-medium transition-colors">Lupa
                                sandi?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="far fa-lock text-slate-400"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="pl-11 w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all pb-3"
                                placeholder="Masukkan kata sandi">
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox"
                            class="h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded cursor-pointer">
                        <label for="remember_me" class="ml-2 block text-sm text-slate-600 cursor-pointer">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" name="btn_login"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold py-4 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 mt-2">
                        Masuk Sistem <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </button>
                </form>

                <div class="mt-8 text-center md:hidden">
                    <p class="text-sm text-slate-500">&copy;
                        <?= date('Y') ?> PINTU KARTANEGARA
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>