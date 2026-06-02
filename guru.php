<?php
// Hubungkan ke database menggunakan konfigurasi koneksi Anda
if (file_exists('koneksi.php')) {
    include_once 'koneksi.php';
} else {
    die("Koneksi database terputus...");
}

// Ambil data guru secara dinamis dari database menggunakan driver MySQLi ($koneksi)
$daftar_guru = [];
$query_guru = "SELECT u.nama, u.email, g.nip, g.mapel, g.jabatan, g.foto 
               FROM user u 
               INNER JOIN guru g ON u.id_user = g.id_user 
               WHERE u.level = 'guru'
               ORDER BY u.nama ASC";

$res_guru = mysqli_query($koneksi, $query_guru);
if ($res_guru) {
    while ($row_g = mysqli_fetch_assoc($res_guru)) {
        $daftar_guru[] = $row_g;
    }
} else {
    die("Gagal mengambil data direktori guru: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Direktori Guru & Tenaga Kependidikan | MTs Al-Hikmah Keo Tengah</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
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
        /* Sembunyikan kursor asli bawaan Windows/Mac hanya pada layar komputer (desktop) */
        @media (min-width: 1024px) {
            html, body, a, button, input, select, .teacher-card {
                cursor: none !important;
            }
            
            /* Titik Inti Kursor */
            .custom-cursor-dot {
                width: 6px;
                height: 6px;
                background-color: #2563EB;
                position: fixed;
                border-radius: 50%;
                pointer-events: none;
                z-index: 9999;
                transform: translate(-50%, -50%);
                transition: width 0.2s, height 0.2s, background-color 0.2s;
            }
            
            /* Lingkaran Luar Kursor dengan Smooth Motion */
            .custom-cursor-ring {
                width: 34px;
                height: 34px;
                border: 1.5px solid rgba(37, 99, 235, 0.4);
                position: fixed;
                border-radius: 50%;
                pointer-events: none;
                z-index: 9998;
                transform: translate(-50%, -50%);
                transition: transform 0.08s ease-out, width 0.3s cubic-bezier(0.25, 1, 0.5, 1), height 0.3s cubic-bezier(0.25, 1, 0.5, 1), background-color 0.3s, border-color 0.3s;
            }
            
            /* Efek ketika kursor diarahkan ke tombol, link, atau kartu yang bisa diklik (Hover State) */
            .custom-cursor-hover .custom-cursor-ring {
                width: 50px;
                height: 50px;
                background-color: rgba(37, 99, 235, 0.06);
                border-color: #2563EB;
            }
            .custom-cursor-hover .custom-cursor-dot {
                width: 10px;
                height: 10px;
                background-color: #1D4ED8;
            }
        }
    </style>
</head>

<body class="bg-bg-base text-text-main antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="custom-cursor-dot" id="cursor-dot"></div>
    <div class="custom-cursor-ring" id="cursor-ring"></div>

    <header class="bg-bg-surface/80 backdrop-blur-xl sticky top-0 z-50 border-b border-slate-100">
        <div class="flex justify-between items-center w-full px-6 md:px-12 max-w-7xl mx-auto h-20">
            <a href="index.php" class="cursor-hover-trigger flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-text-muted hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Beranda
            </a>
            <div class="text-right">
                <span class="text-[10px] font-extrabold text-accent tracking-widest uppercase">Profil Academic</span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 md:px-12 py-16">
        <div class="max-w-2xl mb-12 space-y-3">
            <span class="text-accent font-bold text-xs tracking-widest uppercase block">Dewan Pendidik</span>
            <h1 class="font-serif text-3xl md:text-5xl font-normal text-primary">Direktori Guru & Staf Madrasah</h1>
            <div class="w-12 h-[3px] bg-accent rounded-full mt-2"></div>
            <p class="text-xs text-text-muted font-normal leading-relaxed">Profil resmi tenaga pendidik profesional MTs Al-Hikmah Keo Tengah yang berdedikasi tinggi mengampu pembelajaran sains, kebahasaan, dan khazanah kepesantrenan.</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between mb-10">
            <div class="flex flex-wrap gap-2 w-full md:w-auto text-xs font-bold uppercase tracking-wider">
                <button onclick="setRoleFilter('all', event)" class="role-btn cursor-hover-trigger px-4 py-2.5 bg-primary text-white rounded-xl transition-all duration-200">Semua Staf</button>
                <button onclick="setRoleFilter('Kepala Madrasah', event)" class="role-btn cursor-hover-trigger px-4 py-2.5 bg-slate-50 text-text-muted hover:bg-slate-100 rounded-xl transition-all duration-200">Kepala Madrasah</button>
                <button onclick="setRoleFilter('Guru Tetap', event)" class="role-btn cursor-hover-trigger px-4 py-2.5 bg-slate-50 text-text-muted hover:bg-slate-100 rounded-xl transition-all duration-200">Guru Utama</button>
                <button onclick="setRoleFilter('Tata Usaha / Admin', event)" class="role-btn cursor-hover-trigger px-4 py-2.5 bg-slate-50 text-text-muted hover:bg-slate-100 rounded-xl transition-all duration-200">Staf TU</button>
            </div>

            <div class="relative w-full md:w-80 text-xs">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" id="search-input" oninput="filterTeachers()" placeholder="Cari nama guru atau mata pelajaran..." class="cursor-hover-trigger w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent/10 focus:border-accent text-xs font-normal transition-all text-primary">
            </div>
        </div>

        <div id="teachers-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (!empty($daftar_guru)): ?>
                <?php foreach ($daftar_guru as $guru): 
                    // Menentukan gambar profil default jika data kosong atau file tidak ada
                    $photo_path = (!empty($guru['foto']) && file_exists("uploads/guru/" . $guru['foto'])) ? "uploads/guru/" . $guru['foto'] : "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=400";
                    ?>
                    <div class="teacher-card cursor-hover-trigger bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col justify-between items-center text-center transition-all duration-300 hover:shadow-xl hover:border-slate-200 group" 
                         data-name="<?php echo strtolower($guru['nama']); ?>" 
                         data-subject="<?php echo strtolower($guru['mapel']); ?>" 
                         data-role="<?php echo $guru['jabatan']; ?>"
                         onclick="openTeacherModal('<?php echo htmlspecialchars($guru['nama']); ?>', '<?php echo htmlspecialchars($guru['jabatan']); ?>', '<?php echo htmlspecialchars($guru['mapel']); ?>', '<?php echo !empty($guru['nip']) ? htmlspecialchars($guru['nip']) : '-'; ?>', '<?php echo htmlspecialchars($guru['email']); ?>', '<?php echo $photo_path; ?>')">
                        
                        <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-slate-100 p-1 mb-4 shadow-sm shrink-0 bg-slate-50 group-hover:scale-105 transition-transform duration-300">
                            <img src="<?php echo $photo_path; ?>" alt="<?php echo htmlspecialchars($guru['nama']); ?>" class="w-full h-full object-cover rounded-full">
                        </div>

                        <div class="w-full space-y-1 flex-grow">
                            <span class="text-[9px] font-extrabold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md inline-block mb-1">
                                <?php echo htmlspecialchars($guru['jabatan']); ?>
                            </span>
                            <h3 class="font-bold text-slate-800 text-sm tracking-tight line-clamp-1 group-hover:text-accent transition-colors"><?php echo htmlspecialchars($guru['nama']); ?></h3>
                            <p class="text-[11px] text-text-muted font-medium line-clamp-1"><?php echo htmlspecialchars($guru['mapel']); ?></p>
                        </div>

                        <div class="w-full border-t border-slate-50 mt-4 pt-3 text-[10px] font-mono text-slate-400 space-y-0.5">
                            <div class="text-accent font-semibold text-[9px] uppercase tracking-wider flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                Lihat Detail <span class="material-symbols-outlined text-xs">visibility</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="empty-state" class="col-span-full bg-white p-16 text-center rounded-2xl border border-slate-100 shadow-sm">
                    <span class="material-symbols-outlined text-4xl text-slate-300">person_off</span>
                    <p class="text-xs text-text-muted mt-2 italic font-normal">Tidak ada data dewan guru yang terdaftar dalam pangkalan data.</p>
                </div>
            <?php endif; ?>

            <div id="no-results" class="hidden col-span-full bg-white p-16 text-center rounded-2xl border border-slate-100 shadow-sm">
                <span class="material-symbols-outlined text-4xl text-slate-300">search_off</span>
                <p class="text-xs text-text-muted mt-2 italic font-normal">Nama guru atau mata pelajaran yang Anda cari tidak ditemukan.</p>
            </div>
        </div>
    </main>

    <div id="teacher-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-primary/40 backdrop-blur-md transition-opacity duration-300 opacity-0" onclick="closeTeacherModal(event)">
        <div class="bg-white w-full max-w-xl rounded-3xl overflow-hidden shadow-2xl border border-slate-100 flex flex-col md:flex-row transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            
            <div class="w-full md:w-1/2 aspect-square md:aspect-auto bg-slate-50 relative shrink-0">
                <img id="modal-foto" src="" alt="Foto Profil" class="w-full h-full object-cover">
                <button onclick="hideModal()" class="cursor-hover-trigger md:hidden absolute top-4 right-4 bg-black/40 text-white rounded-full p-1.5 flex items-center justify-center backdrop-blur-sm hover:bg-black/60 transition-colors">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>

            <div class="w-full md:w-1/2 p-6 flex flex-col justify-between relative">
                <button onclick="hideModal()" class="cursor-hover-trigger hidden md:flex absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-50 transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>

                <div class="space-y-4 pt-2">
                    <div>
                        <span id="modal-jabatan" class="text-[9px] font-extrabold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md inline-block mb-2">
                            -
                        </span>
                        <h2 id="modal-nama" class="font-bold text-slate-800 text-base sm:text-lg tracking-tight leading-snug">-</h2>
                    </div>

                    <div class="w-8 h-[2px] bg-accent rounded-full"></div>

                    <div class="space-y-3 text-xs">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-text-muted font-bold uppercase tracking-wider">Mata Pelajaran</span>
                            <span id="modal-mapel" class="text-sm font-semibold text-slate-700 mt-0.5">-</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-text-muted font-bold uppercase tracking-wider">Nomor Induk Pegawai (NIP)</span>
                            <span id="modal-nip" class="text-sm font-mono text-slate-700 mt-0.5">-</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-text-muted font-bold uppercase tracking-wider">Alamat Email</span>
                            <span id="modal-email" class="text-sm text-slate-600 mt-0.5 lowercase truncate">-</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 mt-6 pt-4 text-[9px] uppercase tracking-wider font-semibold text-slate-400 text-center">
                    MTs Al-Hikmah Keo Tengah
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-8 border-t border-slate-100 text-[10px] text-slate-400 uppercase tracking-widest mt-16">
        <p>© 2026 MTs Al-Hikmah Keo Tengah. Direktori Kepegawaian.</p>
    </footer>

    <script>
        const searchInput = document.getElementById('search-input');
        const cards = document.querySelectorAll('.teacher-card');
        const noResults = document.getElementById('no-results');
        const modal = document.getElementById('teacher-modal');
        let currentRole = 'all';

        // ==========================================
        // 1. SCRIPT LOGIKA RE-ANIMATE CUSTOM CURSOR
        // ==========================================
        const dot = document.getElementById('cursor-dot');
        const ring = document.getElementById('cursor-ring');

        // Pastikan kursor hanya bergerak jika diakses lewat komputer / layar besar
        if (window.innerWidth >= 1024) {
            document.addEventListener('mousemove', (e) => {
                // Posisi instan untuk titik inti kursor
                dot.style.left = e.clientX + 'px';
                dot.style.top = e.clientY + 'px';
                
                // Posisi lingkaran luar (memiliki sedikit lag agar berkesan sinematik)
                ring.style.left = e.clientX + 'px';
                ring.style.top = e.clientY + 'px';
            });

            // Berikan pendengar event (event listener) ke semua tombol interaktif
            function initCursorHoverTriggers() {
                document.querySelectorAll('.cursor-hover-trigger').forEach(element => {
                    element.addEventListener('mouseenter', () => {
                        document.body.classList.add('custom-cursor-hover');
                    });
                    element.addEventListener('mouseleave', () => {
                        document.body.classList.remove('custom-cursor-hover');
                    });
                });
            }
            
            // Jalankan pelacak hover saat halaman pertama kali dimuat
            initCursorHoverTriggers();
        } else {
            // Sembunyikan elemen kursor jika dibuka dari HP smartphone / tablet layar sentuh
            dot.style.display = 'none';
            ring.style.display = 'none';
        }

        // ==========================================
        // 2. FUNGSI PENAPIS/FILTER GURU
        // ==========================================
        function setRoleFilter(role, event) {
            currentRole = role;
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-slate-50', 'text-text-muted', 'hover:bg-slate-100');
            });
            event.currentTarget.classList.remove('bg-slate-50', 'text-text-muted', 'hover:bg-slate-100');
            event.currentTarget.classList.add('bg-primary', 'text-white');

            filterTeachers();
        }

        function filterTeachers() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const subject = card.getAttribute('data-subject');
                const role = card.getAttribute('data-role');

                const matchesSearch = name.includes(query) || subject.includes(query);
                const matchesRole = (currentRole === 'all') || (role === currentRole);

                if (matchesSearch && matchesRole) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && cards.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // ==========================================
        // 3. LOGIKA JENDELA POP UP MODAL DETAIL GURU
        // ==========================================
        function openTeacherModal(nama, jabatan, mapel, nip, email, foto) {
            document.getElementById('modal-nama').textContent = nama;
            document.getElementById('modal-jabatan').textContent = jabatan;
            document.getElementById('modal-mapel').textContent = mapel;
            document.getElementById('modal-nip').textContent = nip;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-foto').src = foto;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.transform').classList.remove('scale-95');
                modal.querySelector('.transform').classList.add('scale-100');
            }, 10);
            
            // Hapus kelas hover kursor agar kursor kembali mengecil saat modal terbuka
            document.body.classList.remove('custom-cursor-hover');
        }

        function hideModal() {
            modal.classList.add('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.querySelector('.transform').classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
            document.body.classList.remove('custom-cursor-hover');
        }

        function closeTeacherModal(event) {
            if (event.target === modal) {
                hideModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                hideModal();
            }
        });
    </script>

</body>
</html>