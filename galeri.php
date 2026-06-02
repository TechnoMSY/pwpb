<?php
// Hubungkan ke database menggunakan konfigurasi PDO koneksi Anda
if (file_exists('koneksi.php')) {
    include_once 'koneksi.php';
} else {
    die("Koneksi database terputus...");
}

// Ambil semua data dari tabel berita gabung dengan tabel kategori secara real-time
try {
    $stmt = $pdo->prepare("
        SELECT b.id_berita, b.judul_berita, b.isi_berita, b.gambar, b.tanggal_berita, k.nama_kategori 
        FROM berita b
        INNER JOIN kategori k ON b.id_kategori = k.id_kategori
        ORDER BY b.id_berita DESC
    ");
    $stmt->execute();
    $daftar_galeri = $stmt->fetchAll();
    
    // Ambil daftar kategori yang unik untuk tombol penapis (filter)
    $stmt_k = $pdo->prepare("SELECT * FROM kategori ORDER BY nama_kategori ASC");
    $stmt_k->execute();
    $kategori_list = $stmt_k->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat repositori galeri: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Galeri Kegiatan | MTs Al-Hikmah Keo Tengah</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0F1E36",          
                        "primary-light": "#1E3A63",    
                        "accent": "#4A90E2",           
                        "accent-muted": "#A2C2E8",     
                        "bg-base": "#F8F9FA",          
                        "bg-surface": "#FFFFFF",       
                        "text-main": "#1C2430",        
                        "text-muted": "#606C80"        
                    },
                    fontFamily: {
                        heading: ["Space Grotesk", "sans-serif"],
                        sans: ["Inter", "sans-serif"],
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; letter-spacing: -0.01em; }
        .font-heading-style { font-family: 'Space Grotesk', sans-serif; letter-spacing: -0.03em; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .shadow-editorial { box-shadow: 0 20px 40px -20px rgba(15, 30, 54, 0.05), 0 1px 3px 0 rgba(15, 30, 54, 0.02); }
    </style>
</head>

<body class="bg-bg-base text-text-main antialiased selection:bg-accent-muted/30 selection:text-primary">

    <header class="bg-bg-surface/80 backdrop-blur-md sticky top-0 z-50 border-b border-primary/5">
        <div class="flex justify-between items-center w-full px-6 md:px-16 py-4 max-w-[1440px] mx-auto h-20">
            <div class="flex items-center gap-3 shrink-0">
                <div class="w-9 h-9 bg-primary text-bg-surface rounded-lg flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-lg">school</span>
                </div>
                <div class="text-xs font-bold text-primary tracking-tight font-heading-style uppercase hidden lg:block">
                    MTs Al-Hikmah <span class="text-accent font-medium normal-case">Keo Tengah</span>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-7 text-xs font-semibold uppercase tracking-wider mx-auto">
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="index.php">
                    <span class="material-symbols-outlined text-base">home</span> Beranda
                </a>
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="index.php#profil">
                    <span class="material-symbols-outlined text-base">account_circle</span> Profil
                </a>
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="index.php#keunggulan">
                    <span class="material-symbols-outlined text-base">military_tech</span> Keunggulan
                </a>
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="index.php#berita">
                    <span class="material-symbols-outlined text-base">newspaper</span> Berita
                </a>
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="guru.php">
                    <span class="material-symbols-outlined text-base">badge</span> Guru
                </a>
                <a class="text-primary relative py-2 flex items-center gap-1.5 group" href="galeri.php">
                    <span class="material-symbols-outlined text-base text-accent">gallery_thumbnail</span> Galeri
                    <span class="absolute bottom-0 left-0 w-full h-0.5 bg-accent rounded-full"></span>
                </a>
                <a class="text-text-muted hover:text-primary transition-colors py-2 flex items-center gap-1.5" href="index.php#kontak">
                    <span class="material-symbols-outlined text-base">distance</span> Kontak
                </a>
            </nav>

            <button class="bg-primary text-bg-surface px-5 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-wider hover:bg-primary-light transition-all duration-300 shadow-sm font-heading-style flex items-center gap-1.5 shrink-0">
                <span class="material-symbols-outlined text-sm">login</span> Login SIM
            </button>
        </div>
    </header>

    <main class="max-w-6xl mx-auto w-full px-6 py-12 flex-grow">
        <div class="flex gap-2 w-full overflow-x-auto pb-4 justify-start md:justify-center">
            <button onclick="filterGallery('all', event)" class="gallery-btn bg-primary text-bg-surface px-5 py-2.5 rounded-xl text-xs font-semibold font-heading-style transition-all whitespace-nowrap cursor-pointer">Semua Dokumentasi</button>
            <?php foreach($kategori_list as $kat): ?>
                <button onclick="filterGallery('<?php echo strtolower($kat['nama_kategori']); ?>', event)" class="gallery-btn bg-bg-surface text-text-muted border border-slate-100 px-5 py-2.5 rounded-xl text-xs font-semibold font-heading-style transition-all whitespace-nowrap cursor-pointer">
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8" id="gallery-grid">
            <?php if (count($daftar_galeri) > 0): ?>
                <?php foreach ($daftar_galeri as $item): 
                    $img_src = (!empty($item['gambar']) && file_exists("uploads/berita/" . $item['gambar'])) ? "uploads/berita/" . $item['gambar'] : "https://placehold.co/600x400/E2E8F0/475569?text=Gambar+Kosong";
                    ?>
                    <div class="gallery-item bg-white rounded-2xl border border-slate-100 shadow-editorial overflow-hidden flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300" 
                         data-category="<?php echo strtolower($item['nama_kategori']); ?>">
                        
                        <div class="relative overflow-hidden group aspect-[4/3] bg-slate-50">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($item['judul_berita']); ?>" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <span class="absolute top-4 left-4 text-[9px] font-bold font-heading-style tracking-wider uppercase bg-primary/80 backdrop-blur-md text-white px-2.5 py-1 rounded-md">
                                <?php echo htmlspecialchars($item['nama_kategori']); ?>
                            </span>
                        </div>

                        <div class="p-5 flex-grow flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <h3 class="font-heading-style font-bold text-primary text-sm leading-snug">
                                    <?php echo htmlspecialchars($item['judul_berita']); ?>
                                </h3>
                                <p class="text-[11px] text-text-muted font-light leading-relaxed line-clamp-3">
                                    <?php echo htmlspecialchars(strip_tags($item['isi_berita'])); ?>
                                </p>
                            </div>
                            <div class="border-t border-slate-100 pt-3 mt-4 text-[10px] text-slate-400 font-mono">
                                <?php echo date("d F Y", strtotime($item['tanggal_berita'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-100 shadow-editorial">
                    <span class="material-symbols-outlined text-4xl text-slate-300">broken_image</span>
                    <p class="text-xs text-text-muted mt-2 italic font-light">Belum ada dokumentasi foto yang diinput ke dalam tabel berita.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-primary text-bg-surface rounded-t-[2rem] overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 px-6 md:px-16 py-16 max-w-[1440px] mx-auto">
            <div class="md:col-span-5 space-y-6">
                <div class="font-heading-style text-xl font-bold text-bg-surface uppercase tracking-tight">
                    MTs Al-Hikmah <span class="text-accent-muted font-medium normal-case">Keo Tengah</span>
                </div>
                <p class="text-xs text-bg-base/60 leading-relaxed font-light max-w-sm">
                    Lembaga formal di bawah naungan Kementerian Agama yang konsisten mendidik putra-putri Nagekeo berwawasan global yang bertumpu pada adab Islami.
                </p>
            </div>
            <div class="md:col-span-3 text-xs space-y-3 font-heading-style">
                <h4 class="text-xs font-bold text-accent-muted uppercase tracking-wider mb-4">Eksplorasi</h4>
                <li><a class="text-bg-base/60 hover:text-accent transition-colors list-none block" href="index.php#profil">Riwayat & Profil</a></li>
                <li><a class="text-bg-base/60 hover:text-accent transition-colors list-none block" href="index.php#keunggulan">Program Unggulan</a></li>
                <li><a class="text-bg-base/60 hover:text-accent transition-colors list-none block" href="index.php#berita">Warta Berkala</a></li>
                <li><a class="text-bg-base/60 hover:text-accent transition-colors list-none block" href="guru.php">Direktori Guru</a></li>
                <li><a class="text-bg-base/60 hover:text-accent transition-colors list-none block" href="galeri.php">Galeri Foto</a></li>
            </div>
            <div class="md:col-span-4 text-xs space-y-4">
                <h4 class="font-heading-style text-xs font-bold text-accent-muted uppercase tracking-wider mb-4">Sekretariat</h4>
                <div class="flex items-start gap-3 text-bg-base/60 font-light">
                    <span class="material-symbols-outlined text-accent-muted text-sm mt-0.5">location_on</span>
                    <p>Kecamatan Keo Tengah, Kabupaten Nagekeo, Nusa Tenggara Timur (NTT)</p>
                </div>
            </div>
        </div>
        <div class="border-t border-white/5 py-6 text-center text-[10px] text-bg-base/30 bg-black/20 tracking-wider font-heading-style uppercase">
            <p>© 2026 MTs Al-Hikmah Keo Tengah. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        function filterGallery(category, event) {
            const items = document.querySelectorAll('.gallery-item');
            const buttons = document.querySelectorAll('.gallery-btn');
            
            buttons.forEach(btn => {
                btn.classList.remove('bg-primary', 'text-bg-surface');
                btn.classList.add('bg-bg-surface', 'text-text-muted', 'border', 'border-slate-100');
            });
            
            event.currentTarget.classList.remove('bg-bg-surface', 'text-text-muted', 'border', 'border-slate-100');
            event.currentTarget.classList.add('bg-primary', 'text-bg-surface');

            items.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                
                if (category === 'all' || itemCategory === category) {
                    item.classList.remove('hidden');
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transition = 'opacity 0.3s ease-in';
                    }, 10);
                } else {
                    item.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>