<?php
if (!isset($page)) { die("Direct access strictly prohibited."); }

// 1. Pastikan koneksi database tersedia
if (!isset($koneksi)) {
    if (file_exists('koneksi.php')) {
        include 'koneksi.php';
    }
}

// 2. Inisialisasi awal variabel dengan nilai default 0 (Pola Pelindung Sistem)
$total_pengajar = 0;
$total_pendaftar = 0;
$total_pending_verifikasi = 0;
$total_berita = 0;
$total_kategori = 0;
$total_galeri = 0;
$total_siswa_aktif = 342; // Data statis/simulasi internal madrasah

// 3. Eksekusi query dengan penyesuaian alur logika yang akurat sesuai database mts.sql
if (isset($koneksi) && $koneksi) {
    
    // PERBAIKAN LOGIKA A: Mengubah SUM(jumlah) menjadi COUNT(*) karena user dihitung per baris data
    $query_guru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user WHERE level = 'guru'");
    if ($query_guru) {
        $total_pengajar = mysqli_fetch_assoc($query_guru)['total'] ?? 0;
    }

    // PERBAIKAN LOGIKA B: Mengubah nama tabel dari 'pendaftar' menjadi 'pendaftaran' sesuai DB
    $query_pendaftar = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pendaftaran");
    if ($query_pendaftar) {
        $total_pendaftar = mysqli_fetch_assoc($query_pendaftar)['total'] ?? 0;
    }

    // TAMBAHAN LOGIKA C: Hitung pendaftar yang statusnya masih 'Pending' untuk counter box info
    $query_pending = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pendaftaran WHERE status_verifikasi = 'Pending'");
    if ($query_pending) {
        $total_pending_verifikasi = mysqli_fetch_assoc($query_pending)['total'] ?? 0;
    }

    // PENYESUAIAN LOGIKA D: Hitung total berita aktif dari tabel berita
    $query_berita = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM berita");
    if ($query_berita) {
        $total_berita = mysqli_fetch_assoc($query_berita)['total'] ?? 0;
    }

    // PENYESUAIAN LOGIKA E: Hitung total kategori dari tabel kategori
    $query_kategori = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kategori");
    if ($query_kategori) {
        $total_kategori = mysqli_fetch_assoc($query_kategori)['total'] ?? 0;
    }

    // PENYESUAIAN LOGIKA F: Hitung gambar/galeri aktif (Berita yang memiliki file gambar valid)
    $query_galeri = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM berita WHERE gambar IS NOT NULL AND gambar != 'default.jpg'");
    if ($query_galeri) {
        $total_galeri = mysqli_fetch_assoc($query_galeri)['total'] ?? 0;
    }
}
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
    <div>
        <span class="text-[10px] font-bold tracking-widest text-accent uppercase font-heading">Ringkasan Sistem</span>
        <h1 class="font-heading text-2xl font-bold text-primary mt-0.5">Selamat datang, <?php echo htmlspecialchars($admin_name ?? 'Admin'); ?>.</h1>
        <p class="text-xs text-text-muted font-light mt-1">Hari ini adalah <?php echo date('l, d F Y'); ?> — Pantau integritas data madrasah Anda dari sini.</p>
    </div>
    <div class="flex items-center gap-2">
        <button class="bg-slate-50 border border-slate-200 text-text-main px-4 py-2 rounded-xl text-xs font-semibold shadow-sm hover:bg-slate-100 transition-all flex items-center gap-1.5 font-heading uppercase tracking-wider cursor-pointer">
            <span class="material-symbols-outlined text-base">sync</span> Sinkronisasi Emis
        </button>
        <button class="bg-accent text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm hover:bg-blue-700 transition-all flex items-center gap-1.5 font-heading uppercase tracking-wider cursor-pointer">
            <span class="material-symbols-outlined text-base">download</span> Unduh Laporan
        </button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mt-6">
    
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Siswa</span>
            <div class="w-8 h-8 bg-blue-100 text-accent rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">group</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_siswa_aktif); ?></h3>
            <p class="text-[10px] text-text-muted font-light mt-0.5">Siswa Aktif Terdaftar</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Pendaftar</span>
            <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">person_add</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_pendaftar); ?></h3>
            <p class="text-[10px] text-purple-600 font-medium flex items-center gap-0.5 mt-0.5">
                <span class="material-symbols-outlined text-xs">hourglass_empty</span> <?php echo $total_pending_verifikasi; ?> pending verifikasi
            </p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Pengajar</span>
            <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">co_present</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_pengajar); ?></h3>
            <p class="text-[10px] text-text-muted font-light mt-0.5">Guru / Staf (Level Guru)</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Berita</span>
            <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">newspaper</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_berita); ?></h3>
            <p class="text-[10px] text-text-muted font-light mt-0.5">Artikel warta publikasi</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Galeri</span>
            <div class="w-8 h-8 bg-cyan-100 text-cyan-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">collections</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_galeri); ?></h3>
            <p class="text-[10px] text-text-muted font-light mt-0.5">Dokumentasi media aktif</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-editorial space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted font-heading">Total Kategori</span>
            <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">category</span>
            </div>
        </div>
        <div>
            <h3 class="font-heading text-2xl font-bold text-primary"><?php echo number_format($total_kategori); ?></h3>
            <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-0.5 mt-0.5">
                <span class="material-symbols-outlined text-xs">check_circle</span> Kategori artikel aktif
            </p>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-editorial overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-heading text-xs font-bold text-primary uppercase tracking-wider">Antrean Verifikasi PPDB</h2>
            <span class="text-[10px] bg-blue-50 text-accent font-bold px-2.5 py-0.5 rounded-full font-heading">Real-time</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 font-heading text-[10px] font-bold uppercase tracking-wider text-text-muted border-b border-slate-100">
                        <th class="p-4 pl-6">Nama Lengkap</th>
                        <th class="p-4">NISN</th>
                        <th class="p-4">Asal Sekolah</th>
                        <th class="p-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-light divide-y divide-slate-100">
                    <?php
                    $antrean_ada = false;
                    if (isset($koneksi) && $koneksi) {
                        // Diambil dari data pendaftaran yang status_verifikasi nya bernilai 'Pending'
                        $query_antrean = mysqli_query($koneksi, "SELECT nama_lengkap, nisn, asal_sekolah FROM pendaftaran WHERE status_verifikasi = 'Pending' ORDER BY id_pendaftaran DESC LIMIT 3");
                        if ($query_antrean && mysqli_num_rows($query_antrean) > 0) {
                            $antrean_ada = true;
                            while($row = mysqli_fetch_assoc($query_antrean)) {
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 pl-6 font-medium text-primary"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td class="p-4 text-text-muted font-mono"><?php echo htmlspecialchars($row['nisn']); ?></td>
                                    <td class="p-4 text-text-muted"><?php echo htmlspecialchars($row['asal_sekolah']); ?></td>
                                    <td class="p-4 pr-6 text-right">
                                        <a href="dashboard.php?page=pendaftaran" class="text-accent font-semibold hover:underline">Periksa Berkas</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    if (!$antrean_ada) {
                        echo '<tr><td colspan="4" class="p-6 text-center text-text-muted font-light italic">Belum ada antrean pendaftaran baru dalam database.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-editorial flex flex-col justify-between">
        <div>
            <div class="border-b border-slate-100 pb-3 warmth-title flex items-center justify-between">
                <h3 class="font-heading text-xs font-bold text-primary uppercase tracking-wider">Jejak Audit Sistem</h3>
                <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
            </div>
            <div class="mt-4 space-y-4">
                <div class="flex gap-3 text-xs">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mt-1.5 shrink-0"></div>
                    <div class="space-y-0.5">
                        <p class="text-primary font-medium">Koneksi engine database stabil</p>
                        <p class="text-[10px] text-text-muted font-light">Sistem Terintegrasi Resmi</p>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" class="mt-6 text-center text-[10px] text-text-muted font-bold hover:text-primary transition-colors font-heading uppercase tracking-wider">
            Lihat Log Keamanan Komplit →
        </a>
    </div>
</div>