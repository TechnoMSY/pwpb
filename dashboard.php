<?php
session_start();

// Proteksi Session Ketat - Mode Coding Tingkat Lanjut
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: login.php?error=" . urlencode("Silakan login terlebih dahulu."));
    exit();
}

$admin_name = $_SESSION['user_admin'] ?? 'Administrator';
$user_level = $_SESSION['level'] ?? 'admin';
$kategori_guru = $_SESSION['kategori_guru'] ?? 'Tata Usaha / Admin';

// Jalur Gambar Logo Sekolah Dinamis
$logo_path = "assets/img/logo-mts.png";
$has_custom_logo = file_exists($logo_path);

// Dynamic Routing Map (Mencegah kerentanan Local File Inclusion)
$allowed_pages = [
    'dashboard' => 'home.php',
    'pendaftaran' => 'pendaftaran.php',
    'guru' => 'guru.php',
    'galeri' => 'galeri.php',
    'kategori' => 'kategori.php',
    'pengaturan' => 'pengaturan.php'
];

// Dynamic Routing Map (Mencegah kerentanan Local File Inclusion)
$allowed_pages = [
    'dashboard'   => 'home.php',
    'pendaftaran' => 'pendaftaran.php',
    'guru'        => 'guru.php',     // Tetap ada jika diperlukan atau diakses publik
    'dataguru'    => 'dataguru.php', // TAMBAHKAN BARIS INI UNTUK HALAMAN INPUT ADMIN
    'galeri'      => 'galeri.php',
    'kategori'    => 'kategori.php',
    'pengaturan'  => 'pengaturan.php'
];

// Cari bagian kode $allowed_pages di dashboard.php Anda, lalu tambahkan baris 'datagaleri':
$allowed_pages = [
    'dashboard'   => 'home.php',
    'pendaftaran' => 'pendaftaran.php',
    'guru'        => 'guru.php',
    'dataguru'    => 'dataguru.php',
    'datagaleri'  => 'datagaleri.php', // <-- Masukkan router ini
    'galeri'      => 'galeri.php',
    'kategori'    => 'kategori.php',
    'pengaturan'  => 'pengaturan.php'
];

$page = $_GET['page'] ?? 'dashboard';
$target_file = $allowed_pages[$page] ?? '404.php';

// Jika halaman terdaftar namun file fisiknya belum dibuat, arahkan ke 404.php
if ($target_file !== '404.php' && !file_exists($target_file)) {
    $view_file = '404.php';
} else {
    $view_file = $target_file;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Panel Utama SIM | MTs Al-Hikmah Keo Tengah</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#0F1E36",
                        "accent": "#2563EB",
                        "accent-light": "#EFF6FF",
                        "bg-base": "#FFFFFF",
                        "bg-surface": "#FAFAFA",
                        "text-main": "#0F172A",
                        "text-muted": "#64748B"
                    },
                    fontFamily: { heading: ["Space Grotesk", "sans-serif"], sans: ["Inter", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
        }

        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.02em;
        }

        .shadow-editorial {
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.02), 0 2px 6px -1px rgba(15, 23, 42, 0.01);
        }

        /* FIX TOTAL: Animasi Loading Keren Terkunci Mutlak di Tengah-Tengah Layar */
        #global-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.97);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            /* Berada di atas segala elemen HTML apa pun */
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        /* Desain Cincin Spinner Modern */
        .loader-container {
            position: relative;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-ring-outer {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #2563EB;
            border-bottom-color: #2563EB;
            border-radius: 50%;
            animation: spinClockwise 1.2s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
        }

        .loader-ring-inner {
            position: absolute;
            width: 70%;
            height: 70%;
            border: 3px solid transparent;
            border-left-color: #0F1E36;
            border-right-color: #0F1E36;
            border-radius: 50%;
            animation: spinCounterClockwise 0.8s linear infinite;
        }

        @keyframes spinClockwise {
            0% {
                transform: rotate(0deg);
                scale: 1;
            }

            50% {
                transform: rotate(180deg);
                scale: 1.1;
            }

            100% {
                transform: rotate(360deg);
                scale: 1;
            }
        }

        @keyframes spinCounterClockwise {
            to {
                transform: rotate(-360deg);
            }
        }
    </style>
</head>

<body class="bg-bg-base text-text-main antialiased min-h-screen selection:bg-accent selection:text-white">

    <div id="global-preloader">
        <div class="flex flex-col items-center gap-5">
            <div class="loader-container">
                <div class="loader-ring-outer"></div>
                <div class="loader-ring-inner"></div>
            </div>
            <div class="text-center space-y-1">
                <span class="block text-xs font-heading font-bold uppercase tracking-widest text-primary">
                    Memuat Sistem
                </span>
                <span class="block text-[10px] font-sans font-light text-text-muted tracking-normal animate-pulse">
                    Menyelaraskan Integritas Data...
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen gap-0">

        <main class="col-span-1 lg:col-span-9 p-6 sm:p-10 order-1 overflow-y-auto">
            <?php include $view_file; ?>
        </main>

        <nav
            class="col-span-1 lg:col-span-3 bg-white text-text-main p-6 sm:p-8 flex flex-col justify-between order-2 border-t lg:border-t-0 lg:border-l border-slate-100 relative z-30 shadow-sm">
            <div class="space-y-8">

                <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-accent-light text-accent rounded-xl flex items-center justify-center overflow-hidden border border-blue-100">
                            <?php if ($has_custom_logo): ?>
                                <img src="<?php echo $logo_path; ?>" alt="Logo Madrasah" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="material-symbols-outlined text-xl font-bold">school</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold tracking-tight font-heading text-primary uppercase">
                                MTs Al-Hikmah
                                <span
                                    class="text-text-muted font-medium normal-case block text-[10px] tracking-normal -mt-0.5">Keo
                                    Tengah</span>
                            </div>
                        </div>
                    </div>
                    <span
                        class="text-[9px] border border-slate-200 px-2 py-0.5 rounded text-accent bg-accent-light uppercase font-bold tracking-wider font-heading">
                        <?php echo htmlspecialchars(strtoupper($user_level)); ?>
                    </span>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl flex items-center gap-3 border border-slate-100">
                    <div
                        class="w-9 h-9 bg-primary text-white rounded-lg flex items-center justify-center font-heading font-bold text-sm">
                        <?php echo strtoupper(substr($admin_name, 0, 2)); ?>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="text-xs font-bold font-heading tracking-wide text-primary truncate">
                            <?php echo htmlspecialchars($admin_name); ?></h4>
                        <p class="text-[10px] text-text-muted font-light truncate">
                            <?php echo htmlspecialchars($kategori_guru); ?></p>
                    </div>
                </div>

                <div class="space-y-1">
                    <span
                        class="block text-[9px] font-bold text-text-muted uppercase tracking-widest px-2 mb-2 font-heading">Navigasi
                        Utama</span>

                    <a href="dashboard.php?page=dashboard"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'dashboard' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'dashboard' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">dashboard</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="dashboard.php?page=pendaftaran"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'pendaftaran' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'pendaftaran' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">how_to_reg</span>
                        <span>Pendaftaran</span>
                    </a>

                    <a href="dashboard.php?page=dataguru"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'dataguru' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'dataguru' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">badge</span>
                        <span>Data Guru</span>
                    </a>

                    <a href="dashboard.php?page=datagaleri"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'datagaleri' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'datagaleri' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">collections</span>
                        <span>Galeri Berita</span>
                    </a>

                    <a href="dashboard.php?page=kategori"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'kategori' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'kategori' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">category</span>
                        <span>Kategori</span>
                    </a>

                    <a href="dashboard.php?page=pengaturan"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $page === 'pengaturan' ? 'bg-accent-light text-accent font-bold shadow-sm' : 'text-text-muted hover:bg-slate-50 hover:text-primary'; ?> text-xs font-heading tracking-wide transition-all group border-t border-slate-100 mt-4 pt-4">
                        <span
                            class="material-symbols-outlined text-lg transition-colors <?php echo $page === 'pengaturan' ? 'text-accent' : 'text-text-muted group-hover:text-primary'; ?>">settings</span>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 mt-10">
                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari SIM MTs Al-Hikmah?');"
                    class="flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-bold text-xs uppercase tracking-wider transition-all duration-300 font-heading shadow-md">
                    <span class="material-symbols-outlined text-base">logout</span> Keluar Sistem
                </a>
                <p class="text-center text-[9px] text-text-muted/40 mt-4 font-heading tracking-wider uppercase">SIM
                    v1.0.0 © 2026</p>
            </div>
        </nav>

    </div>

    <script>
        const preloader = document.getElementById('global-preloader');

        // 1. Matikan preloader saat REFRESH atau pertama kali halaman dimuat
        window.addEventListener('load', () => {
            // Memberikan jeda waktu 400ms agar animasi loading di tengah layar terlihat halus saat refresh
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.visibility = 'hidden';
                    preloader.style.display = 'none';
                }, 300); // Waktu animasi memudar (fade-out)
            }, 400); // <--- Atur durasi tampil minimum saat refresh di sini (400ms)
        });

        // 2. Interseptor klik menu: Memunculkan kembali animasi loading tepat di tengah layar sebelum dialihkan
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && this.getAttribute('target') !== '_blank') {
                    preloader.style.display = 'flex';
                    preloader.style.visibility = 'visible';
                    preloader.style.opacity = '1';
                }
            });
        });
    </script>
</body>

</html>