<?php
if (file_exists('koneksi.php')) {
    include_once 'koneksi.php';
} else {
    die("Sistem database bermasalah...");
}

$notif_sukses = "";
$notif_error = "";

if (isset($_POST['kirim_formulir'])) {
    $nama_siswa   = trim($_POST['nama_siswa']); 
    $nisn         = trim($_POST['nisn']);
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $no_hp        = trim($_POST['no_hp']);

    if (empty($nama_siswa) || empty($nisn) || empty($asal_sekolah) || empty($no_hp)) {
        $notif_error = "Harap isi seluruh kolom formulir pendaftaran dengan lengkap.";
    } else {
        try {
            // Pembuatan nomor registrasi unik dinamis sesuai konstrain UNIQUE di database
            $no_pendaftaran = "PPDB-" . date('Ymd') . "-" . rand(100, 999);

            // Pengisian nilai bawaan otomatis untuk menghindari error kolom mandatory database (NOT NULL)
            $nik_default          = "0000000000000000";
            $tempat_lahir_default = "Nagekeo";
            $tanggal_lahir_default= "2013-01-01";
            $jk_default           = "L"; 
            $nama_ayah_default    = "-";
            $nama_ibu_default     = "-";
            $alamat_default       = "Keo Tengah";

            // Menggunakan MySQLi sesuai dengan standar driver pendaftaran.php
            $query = "INSERT INTO pendaftaran (
                        no_pendaftaran, nama_lengkap, nisn, nik, tempat_lahir, 
                        tanggal_lahir, jenis_kelamin, asal_sekolah, nama_ayah, 
                        nama_ibu, no_hp_ortu, alamat_siswa, status_verifikasi, id_user
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 1)";
            
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssssssssssss", 
                $no_pendaftaran,
                $nama_siswa, 
                $nisn, 
                $nik_default,
                $tempat_lahir_default,
                $tanggal_lahir_default,
                $jk_default,
                $asal_sekolah, 
                $nama_ayah_default,
                $nama_ibu_default,
                $no_hp, 
                $alamat_default
            );

            if (mysqli_stmt_execute($stmt)) {
                $notif_sukses = "Registrasi Anda Berhasil! Simpan No Pendaftaran Anda: <strong>$no_pendaftaran</strong> berkas Anda akan segera diverifikasi oleh tim panitia.";
            } else {
                $notif_error = "Gagal menyimpan berkas pendaftaran: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);

        } catch (Exception $e) {
            $notif_error = "Gagal memproses registrasi: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Formulir PPDB Online | MTs Al-Hikmah Keo Tengah</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F8FAFC] text-[#0F172A] antialiased min-h-screen flex flex-col justify-between" style="font-family:'Plus Jakarta Sans', sans-serif;">

    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 py-4 px-6 md:px-12 border-b border-slate-100 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-primary transition-colors">
            ⬅️ Kembali ke Beranda
        </a>
        <div class="text-right">
            <span class="text-[10px] font-extrabold text-blue-600 tracking-widest uppercase">TA 2026/2027</span>
        </div>
    </header>

    <main class="max-w-lg mx-auto w-full px-6 py-12 flex-grow flex items-center">
        <div class="bg-white p-8 md:p-10 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 w-full">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-extrabold text-[#0B1523] tracking-tight">Formulir PPDB Online</h2>
                <p class="text-xs text-slate-400 mt-1.5">Lengkapi data pendaftaran calon peserta didik dengan data yang valid.</p>
            </div>

            <?php if (!empty($notif_sukses)): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 p-4 rounded-xl text-xs leading-relaxed">
                    <?php echo $notif_sukses; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($notif_error)): ?>
                <div class="mb-6 bg-red-50 border border-red-100 text-red-800 p-4 rounded-xl text-xs">
                    <?php echo $notif_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Lengkap Calon Siswa</label>
                    <input type="text" name="nama_siswa" required placeholder="Sesuai Ijazah SD / Akta Kelahiran..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nomor Induk Siswa Nasional (NISN)</label>
                    <input type="number" name="nisn" required placeholder="10 Digit nomor NISN resmi..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Asal Sekolah (SD/MI)</label>
                    <input type="text" name="asal_sekolah" required placeholder="Contoh: SDN Keo Tengah, MIS Al-Hikmah..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nomor HP / WhatsApp Orang Tua</label>
                    <input type="tel" name="no_hp" required placeholder="Contoh: 081234567890" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-sm">
                </div>

                <button type="submit" name="kirim_formulir" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold hover:bg-blue-700 transition-all text-xs uppercase tracking-wider shadow-lg shadow-blue-500/20 cursor-pointer mt-4">
                    Kirim Berkas Formulir
                </button>
            </form>
        </div>
    </main>

    <footer class="text-center py-6 border-t border-slate-200 text-[10px] text-slate-400">
        <p>© 2026 MTs Al-Hikmah Keo Tengah. PPDB Online System.</p>
    </footer>

</body>
</html>