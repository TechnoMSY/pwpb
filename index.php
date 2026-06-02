<?php
// Hubungkan ke database menggunakan konfigurasi database koneksi Anda
if (file_exists('koneksi.php')) {
    include_once 'koneksi.php';
} else {
    die("Koneksi database terputus...");
}

// Ambil 3 data berita/galeri terbaru untuk ditampilkan di section Berita & Artikel beranda
$berita_terbaru = [];
$query_news = "SELECT b.id_berita, b.judul_berita, b.isi_berita, b.gambar, b.tanggal_berita, k.nama_kategori 
               FROM berita b
               INNER JOIN kategori k ON b.id_kategori = k.id_kategori
               ORDER BY b.id_berita DESC 
               LIMIT 3";
$res_news = mysqli_query($koneksi, $query_news);
if ($res_news) {
    while ($row_news = mysqli_fetch_assoc($res_news)) {
        $berita_terbaru[] = $row_news;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>MTs Al-Hikmah Keo Tengah | Mewujudkan Generasi Cerdas dan Berakhlak Mulia</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0B1523",
                        "primary-light": "#17263D",
                        "accent": "#2563EB",
                        "accent-hover": "#1D4ED8",
                        "bg-base": "#F8FAFC",
                        "bg-surface": "#FFFFFF",
                        "text-main": "#0F172A",
                        "text-muted": "#64748B"
                    },
                    fontFamily: {
                        heading: ["Plus Jakarta Sans", "sans-serif"],
                        serif: ["Playfair Display", "serif"],
                        sans: ["Plus Jakarta Sans", "sans-serif"],
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .shadow-modern {
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.04), 0 1px 3px 0 rgba(15, 23, 42, 0.01);
        }
        .shadow-premium {
            box-shadow: 0 25px 50px -12px rgba(11, 21, 35, 0.08);
        }
    </style>
</head>

<body class="bg-bg-base text-text-main antialiased selection:bg-accent/10 selection:text-accent">

    <header class="bg-white/80 backdrop-blur-xl sticky top-0 z-50 border-b border-slate-100/80 transition-all duration-300 shadow-sm shadow-slate-100/50">
        <div class="flex justify-between items-center w-full px-6 md:px-12 max-w-7xl mx-auto h-20">
            
            <div class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 bg-primary text-bg-surface rounded-xl flex items-center justify-center shadow-md shadow-primary/10">
                    <span class="material-symbols-outlined text-xl">school</span>
                </div>
                <div>
                    <div class="text-xs font-extrabold text-primary tracking-wider uppercase font-heading">
                        MTs Al-Hikmah
                    </div>
                    <div class="text-[10px] text-accent font-semibold tracking-wide uppercase">Keo Tengah</div>
                </div>
            </div>

            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold uppercase tracking-wider">
                <a class="text-accent border-b-2 border-accent py-2 transition-all" href="#">Beranda</a>
                <a class="text-text-muted hover:text-primary hover:border-b-2 hover:border-primary py-2 transition-all" href="#profil">Profil</a>
                <a class="text-text-muted hover:text-primary hover:border-b-2 hover:border-primary py-2 transition-all" href="#keunggulan">Keunggulan</a>
                
                <a class="text-text-muted hover:text-primary hover:border-b-2 hover:border-primary py-2 transition-all flex items-center gap-1" href="guru.php">
                    Direktori Guru
                </a>
                
                <a class="text-text-muted hover:text-primary hover:border-b-2 hover:border-primary py-2 transition-all" href="#berita">Berita</a>
                <a class="text-text-muted hover:text-primary hover:border-b-2 hover:border-primary py-2 transition-all" href="#kontak">Kontak</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="formulir-ppdb.php" class="hidden sm:flex bg-accent text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-accent-hover transition-all duration-300 shadow-lg shadow-accent/20 items-center gap-2">
                    <span class="material-symbols-outlined text-sm">assignment</span> PPDB Online
                </a>
                
                <a href="login.php" class="p-2.5 bg-slate-100 hover:bg-primary hover:text-white text-slate-700 rounded-xl transition-all duration-300 flex items-center justify-center shadow-inner" title="Masuk Panel Admin">
                    <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                </a>
                
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 text-slate-700 hover:bg-slate-50 rounded-xl focus:outline-none flex items-center justify-center" aria-label="Buka Menu">
                    <span id="menu-icon" class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-100 bg-white/95 backdrop-blur-lg px-6 py-4 space-y-3 shadow-xl text-xs font-bold uppercase tracking-wider">
            <a onclick="toggleMobileMenu()" class="block text-accent py-2.5 px-3 rounded-xl hover:bg-slate-50" href="#">Beranda</a>
            <a onclick="toggleMobileMenu()" class="block text-text-muted hover:text-primary py-2.5 px-3 rounded-xl hover:bg-slate-50" href="#profil">Profil</a>
            <a onclick="toggleMobileMenu()" class="block text-text-muted hover:text-primary py-2.5 px-3 rounded-xl hover:bg-slate-50" href="#keunggulan">Keunggulan</a>
            
            <a onclick="toggleMobileMenu()" class="block text-text-muted hover:text-primary py-2.5 px-3 rounded-xl hover:bg-slate-50" href="guru.php">Direktori Guru</a>
            
            <a onclick="toggleMobileMenu()" class="block text-text-muted hover:text-primary py-2.5 px-3 rounded-xl hover:bg-slate-50" href="#berita">Berita</a>
            <a onclick="toggleMobileMenu()" class="block text-text-muted hover:text-primary py-2.5 px-3 rounded-xl hover:bg-slate-50" href="#kontak">Kontak</a>
            
            <a onclick="toggleMobileMenu()" class="block text-amber-600 hover:text-amber-700 py-2.5 px-3 rounded-xl hover:bg-amber-50/50 flex items-center gap-1.5" href="login.php">
                <span class="material-symbols-outlined text-sm">lock</span> Login Dashboard Admin
            </a>
            
            <div class="pt-2 border-t border-slate-100">
                <a onclick="toggleMobileMenu()" href="formulir-ppdb.php" class="w-full bg-accent text-white py-3 rounded-xl text-center font-bold uppercase tracking-wider block shadow-md shadow-accent/10">
                    PPDB Online
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="px-4 md:px-8 py-6 max-w-7xl mx-auto">
            <div class="relative min-h-[640px] flex items-center rounded-[2rem] overflow-hidden bg-primary shadow-premium">
                <div class="absolute inset-0 bg-gradient-to-r from-primary via-primary/60 to-transparent z-10"></div>

                <img id="hero-slider-image" alt="Siswa MTs Al-Hikmah Keo Tengah" class="absolute inset-0 w-full h-full object-cover opacity-40" src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=1440">

                <div class="relative z-20 px-6 sm:px-12 md:px-16 py-12 max-w-2xl text-bg-surface ml-4 md:ml-8">
                    <span class="inline-flex items-center gap-1.5 border border-white/20 text-white text-[10px] font-bold tracking-widest uppercase px-3 py-1.5 rounded-full mb-6 bg-white/10 backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Nagekeo, Nusa Tenggara Timur
                    </span>

                    <h1 class="font-serif italic text-4xl sm:text-5xl md:text-6xl font-normal mb-6 leading-[1.1] text-bg-surface">
                        Mewujudkan Generasi <span class="font-sans font-black not-italic text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-200">Cerdas & Berakhlak</span> Mulia
                    </h1>

                    <p class="text-xs md:text-sm mb-8 text-slate-300 leading-relaxed font-normal max-w-lg">
                        Integrasi keunggulan sains akademik dengan nilai luhur pesantren demi mencetak pemimpin masa depan di daratan Flores.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="formulir-ppdb.php" class="bg-white text-primary px-6 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-100 transition-all duration-300 shadow-xl flex items-center gap-2">
                            Daftar Sekarang <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                        <a href="#profil" class="border border-white/30 text-white px-6 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-white/10 transition-all duration-300">
                            Pelajari Profil
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="profil" class="px-6 md:px-12 py-24 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-5 relative">
                    <div class="aspect-[3/4] rounded-[2rem] overflow-hidden bg-bg-surface p-4 shadow-modern border border-slate-100">
                        <img alt="Kepala MTs Al-Hikmah" class="w-full h-full object-cover rounded-[1.5rem]" src="https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&q=80&w=600">
                    </div>
                    <div class="absolute -bottom-6 -right-4 bg-primary text-bg-surface p-6 rounded-2xl shadow-xl max-w-xs border border-slate-800">
                        <p class="text-sm font-bold font-heading">Firman Haris, S.Pd.</p>
                        <p class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mt-0.5">Kepala Madrasah</p>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="space-y-2">
                        <span class="text-accent font-bold text-xs tracking-widest uppercase block">Khutbah Iftitah</span>
                        <h2 class="font-serif text-3xl md:text-5xl font-normal text-primary">Sambutan Kepala Madrasah</h2>
                    </div>
                    <div class="w-12 h-[3px] bg-accent rounded-full"></div>
                    <div class="text-text-muted space-y-4 leading-relaxed text-sm font-normal">
                        <p class="font-bold text-primary">Assalamualaikum Warahmatullahi Wabarakatuh,</p>
                        <p>
                            Selamat datang di beranda digital <strong>MTs Al-Hikmah Keo Tengah</strong>. Website ini kami rancang sebagai jendela transparansi dan ruang komunikasi inklusif bagi seluruh wali santri, masyarakat, dan pemerhati pendidikan Islam.
                        </p>
                        <p>
                            Di bawah naungan bumi Nagekeo, kami mendedikasikan diri untuk merawat fitrah generasi muda melalui keseimbangan ilmu umum (<em>IPTEK</em>) dan kekuatan spiritual (<em>IMTAK</em>), melahirkan pribadi tangguh yang adaptif namun tetap beradab tinggi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section id="keunggulan" class="bg-white py-24 border-y border-slate-100">
            <div class="px-6 md:px-12 max-w-7xl mx-auto">
                <div class="max-w-xl mb-16 space-y-2">
                    <span class="text-accent font-bold text-xs tracking-widest uppercase">Pilar Utama</span>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-primary tracking-tight">Karakter Unggulan Kami</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php
                    $features = [
                        ['menu_book', 'Sains & Salafiah', 'Penyelarasan harmonis kurikulum kemendikbud dengan kajian kitab kuning fundamental.'],
                        ['architecture', 'Fasilitas Kontekstual', 'Laboratorium alam terbuka, pojok literasi Flores, dan kelas multimedia interaktif.'],
                        ['psychology', 'Bakat & Minat', 'Pendampingan intensif program hifdzil Quran, olahraga beregu, serta seni bela diri.'],
                        ['workspace_premium', 'Kultur Akhlak', 'Internalisasi pembiasaan salat dhuha berjamaah dan penanaman adab kesantunan nusantara.']
                    ];
                    foreach ($features as $f):
                    ?>
                    <div class="bg-bg-base p-8 rounded-2xl shadow-modern border border-slate-100 hover:border-accent/20 hover:bg-white hover:shadow-xl transition-all duration-300 group">
                        <div class="w-12 h-12 bg-white text-primary rounded-xl flex items-center justify-center mb-6 group-hover:bg-accent group-hover:text-white transition-all duration-300 shadow-sm border border-slate-100">
                            <span class="material-symbols-outlined text-lg"><?= $f[0] ?></span>
                        </div>
                        <h3 class="font-bold text-base text-primary mb-3"><?= $f[1] ?></h3>
                        <p class="text-xs text-text-muted leading-relaxed"><?= $f[2] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 md:px-12 py-24" id="berita">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <span class="text-[10px] font-bold tracking-widest text-accent uppercase block mb-2">Warta Berkala</span>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-primary tracking-tight">Berita & Kegiatan</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($berita_terbaru)): ?>
                    <?php foreach ($berita_terbaru as $news):
                        $img_path = (!empty($news['gambar']) && file_exists("uploads/berita/" . $news['gambar'])) ? "uploads/berita/" . $news['gambar'] : "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&q=80&w=600";
                        ?>
                        <div class="bg-bg-surface rounded-2xl border border-slate-100 shadow-modern overflow-hidden flex flex-col justify-between hover:shadow-xl transition-all duration-300 group">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                <img alt="<?php echo htmlspecialchars($news['judul_berita']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?php echo $img_path; ?>">
                                <span class="absolute top-4 left-4 text-[9px] font-bold tracking-wider uppercase bg-primary text-white px-2.5 py-1 rounded-md">
                                    <?php echo htmlspecialchars($news['nama_kategori']); ?>
                                </span>
                            </div>

                            <div class="p-6 flex-grow flex flex-col justify-between">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 text-[10px] text-text-muted font-mono">
                                        <span class="material-symbols-outlined text-xs">calendar_month</span>
                                        <span><?php echo date("d M Y", strtotime($news['tanggal_berita'])); ?></span>
                                    </div>
                                    <h3 class="font-bold text-primary text-base leading-snug line-clamp-2 hover:text-accent transition-colors">
                                        <?php echo htmlspecialchars($news['judul_berita']); ?>
                                    </h3>
                                    <p class="text-xs text-text-muted font-normal leading-relaxed line-clamp-3">
                                        <?php echo htmlspecialchars(strip_tags($news['isi_berita'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-100 shadow-modern">
                        <span class="material-symbols-outlined text-4xl text-slate-300">newspaper</span>
                        <p class="text-xs text-text-muted mt-2 italic font-normal">Belum ada warta berita atau dokumentasi kegiatan yang diterbitkan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer id="kontak" class="bg-primary text-slate-400 rounded-t-[2.5rem] overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 px-6 md:px-12 py-16 max-w-7xl mx-auto">
            <div class="md:col-span-6 space-y-6">
                <div class="text-lg font-black text-bg-surface uppercase tracking-wider">
                    MTs Al-Hikmah <span class="text-accent font-normal normal-case block text-xs tracking-widest mt-1">KEO TENGAH</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                    Lembaga formal di bawah naungan Kementerian Agama yang konsisten mendidik putra-putri Nagekeo berwawasan global yang bertumpu pada adab Islami.
                </p>
            </div>

            <div class="md:col-span-6 text-xs space-y-4 md:text-right">
                <h4 class="text-xs font-bold text-bg-surface uppercase tracking-wider mb-4">Sekretariat</h4>
                <p class="max-w-xs md:ml-auto">Kecamatan Keo Tengah, Kabupaten Nagekeo, Nusa Tenggara Timur (NTT)</p>
                <p class="text-bg-surface font-semibold">+62 812-3456-7890</p>
            </div>
        </div>
        <div class="border-t border-slate-800 py-6 text-center text-[10px] text-slate-500 bg-black/10 uppercase tracking-widest">
            <p>© 2026 MTs Al-Hikmah Keo Tengah. All Rights Reserved</p>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.textContent = 'close';
            } else {
                menu.classList.add('hidden');
                icon.textContent = 'menu';
            }
        }
    </script>

</body>
</html>