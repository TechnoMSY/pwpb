<?php
// Proteksi halaman agar tidak bisa diakses langsung tanpa melalui dashboard.php
if (!isset($page)) { die("Direct access strictly prohibited."); }

// Pengecekan file koneksi.php
if (!isset($koneksi) || empty($koneksi)) {
    if (file_exists(__DIR__ . '/koneksi.php')) {
        include_once __DIR__ . '/koneksi.php';
    } elseif (file_exists('koneksi.php')) {
        include_once 'koneksi.php';
    } elseif (file_exists('../koneksi.php')) {
        include_once '../koneksi.php';
    }
}

if (!isset($koneksi) || !$koneksi) {
    die("<div style='padding:20px; color:red; background:#fff5f5; border:1px solid #ffe3e3; border-radius:8px;'><strong>Sistem Eror:</strong> Koneksi database gagal.</div>");
}

$pesan_sukses = "";
$pesan_error = "";
// Ambil ID User dari session login dashboard untuk track pembuat galeri/berita
$id_user_login = $_SESSION['id_user'] ?? 1; 

// ==========================================
// AMBIL DATA BERITA UNTUK MODE EDIT
// ==========================================
$galeri_edit = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_edit = intval($_GET['id']);
    $stmt_fetch = mysqli_prepare($koneksi, "SELECT id_berita, judul_berita, isi_berita, id_kategori, gambar FROM berita WHERE id_berita = ?");
    mysqli_stmt_bind_param($stmt_fetch, "i", $id_edit);
    mysqli_stmt_execute($stmt_fetch);
    $res_edit = mysqli_stmt_get_result($stmt_fetch);
    $galeri_edit = mysqli_fetch_assoc($res_edit);
    mysqli_stmt_close($stmt_fetch);
}

// ==========================================
// PROSES SIMPAN (TAMBAH / UPDATE) DATA BERITA
// ==========================================
if (isset($_POST['simpan_galeri'])) {
    $mode        = $_POST['mode']; 
    $id_berita   = isset($_POST['id_berita']) ? intval($_POST['id_berita']) : 0;
    $judul       = trim($_POST['judul_berita']);
    $isi_berita  = trim($_POST['isi_berita']);
    $id_kategori = intval($_POST['id_kategori']);
    
    if (empty($judul) || empty($id_kategori) || empty($isi_berita)) {
        $pesan_error = "Semua kolom form wajib diisi lengkap.";
    } else {
        $gambar_sekarang = "default.jpg";
        if ($mode == 'edit') {
            $stmt_img = mysqli_prepare($koneksi, "SELECT gambar FROM berita WHERE id_berita = ?");
            mysqli_stmt_bind_param($stmt_img, "i", $id_berita);
            mysqli_stmt_execute($stmt_img);
            mysqli_stmt_bind_result($stmt_img, $gambar_sekarang);
            mysqli_stmt_fetch($stmt_img);
            mysqli_stmt_close($stmt_img);
        }

        $nama_gambar_db = $gambar_sekarang; 
        $upload_ok = true;

        // Proses unggah gambar baru jika ada file masuk
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            
            $info          = getimagesize($file_tmp);
            $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
            
            if ($file_size > 3 * 1024 * 1024) {
                $pesan_error = "Ukuran gambar terlalu besar. Maksimal 3MB.";
                $upload_ok = false;
            } elseif (!$info || !in_array($info[2], $allowed_types)) {
                $pesan_error = "Format berkas salah. Gunakan gambar JPG/PNG.";
                $upload_ok = false;
            } else {
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $nama_gambar_db = "berita_" . bin2hex(random_bytes(8)) . "." . $ext;
                $target_dir = __DIR__ . "/uploads/berita/";
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                if (move_uploaded_file($file_tmp, $target_dir . $nama_gambar_db)) {
                    if ($mode == 'edit' && !empty($gambar_sekarang) && $gambar_sekarang != 'default.jpg' && file_exists($target_dir . $gambar_sekarang)) {
                        @unlink($target_dir . $gambar_sekarang);
                    }
                } else {
                    $pesan_error = "Gagal memindahkan gambar ke folder penyimpanan server.";
                    $upload_ok = false;
                }
            }
        } elseif ($mode == 'tambah') {
            $pesan_error = "Wajib mengunggah berkas foto utama untuk dokumentasi galeri.";
            $upload_ok = false;
        }

        // Eksekusi Query Database
        if ($upload_ok) {
            if ($mode == 'tambah') {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO berita (judul_berita, isi_berita, gambar, id_kategori, id_user) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssii", $judul, $isi_berita, $nama_gambar_db, $id_kategori, $id_user_login);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan_sukses = "Data galeri kegiatan berhasil dipublikasikan.";
                } else {
                    $pesan_error = "Gagal menyimpan data: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            } elseif ($mode == 'edit') {
                $stmt = mysqli_prepare($koneksi, "UPDATE berita SET judul_berita=?, isi_berita=?, gambar=?, id_kategori=? WHERE id_berita=?");
                mysqli_stmt_bind_param($stmt, "sssii", $judul, $isi_berita, $nama_gambar_db, $id_kategori, $id_berita);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan_sukses = "Data dokumentasi galeri berhasil diubah.";
                    $galeri_edit = null;
                } else {
                    $pesan_error = "Gagal merubah data: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// ==========================================
// PROSES HAPUS DATA BERITA
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    $stmt_img = mysqli_prepare($koneksi, "SELECT gambar FROM berita WHERE id_berita = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id_hapus);
    mysqli_stmt_execute($stmt_img);
    mysqli_stmt_bind_result($stmt_img, $gambar_lama);
    mysqli_stmt_fetch($stmt_img);
    mysqli_stmt_close($stmt_img);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM berita WHERE id_berita=$id_hapus");
    if ($query_hapus) {
        if (!empty($gambar_lama) && $gambar_lama != 'default.jpg' && file_exists(__DIR__ . "/uploads/berita/" . $gambar_lama)) {
            @unlink(__DIR__ . "/uploads/berita/" . $gambar_lama);
        }
        $pesan_sukses = "Data dokumentasi galeri berhasil dihapus dari sistem.";
    } else {
        $pesan_error = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}
?>

<?php if (!empty($pesan_sukses)): ?>
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs flex items-center gap-2">
        <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
        <div><?php echo $pesan_sukses; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($pesan_error)): ?>
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl text-xs flex items-center gap-2">
        <span class="material-symbols-outlined text-base text-red-600">error</span>
        <div><?php echo $pesan_error; ?></div>
    </div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row gap-6 mt-2">
    <div class="w-full lg:w-1/3 bg-white p-6 rounded-2xl border border-slate-100 shadow-editorial h-fit">
        <div class="border-b border-slate-100 pb-3 mb-5 flex justify-between items-center">
            <div>
                <span class="text-[10px] font-bold tracking-widest text-accent uppercase font-heading">Manajemen Galeri</span>
                <h2 class="font-heading text-base font-bold text-primary mt-0.5">
                    <?php echo $galeri_edit ? 'Ubah Dokumentasi' : 'Tambah Galeri / Berita'; ?>
                </h2>
            </div>
            <?php if ($galeri_edit): ?>
                <a href="dashboard.php?page=datagaleri" class="text-[10px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-medium hover:bg-slate-200 transition-colors">Batal</a>
            <?php endif; ?>
        </div>

        <form action="dashboard.php?page=datagaleri" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" name="mode" value="<?php echo $galeri_edit ? 'edit' : 'tambah'; ?>">
            <?php if ($galeri_edit): ?>
                <input type="hidden" name="id_berita" value="<?php echo $galeri_edit['id_berita']; ?>">
            <?php endif; ?>

            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Judul Dokumentasi</label>
                <input type="text" name="judul_berita" value="<?php echo $galeri_edit ? htmlspecialchars($galeri_edit['judul_berita']) : ''; ?>" placeholder="Masukkan judul berita atau kegiatan sekolah..." required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>
            
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Pilih Kategori</label>
                <select name="id_kategori" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
                    <option value="">-- Pilih Kategori Berita/Kegiatan --</option>
                    <?php
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                    while($k = mysqli_fetch_assoc($q_kat)){
                        $selected = ($galeri_edit && $galeri_edit['id_kategori'] == $k['id_kategori']) ? 'selected' : '';
                        echo "<option value='".$k['id_kategori']."' $selected>".htmlspecialchars($k['nama_kategori'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Deskripsi Lengkap / Isi Berita</label>
                <textarea name="isi_berita" rows="5" placeholder="Tulis rincian jalannya kegiatan secara lengkap disini..." required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary"><?php echo $galeri_edit ? htmlspecialchars($galeri_edit['isi_berita']) : ''; ?></textarea>
            </div>
            
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">File Gambar Sampul (Maks 3MB)</label>
                <?php if ($galeri_edit && !empty($galeri_edit['gambar'])): ?>
                    <div class="mb-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <img src="uploads/berita/<?php echo $galeri_edit['gambar']; ?>" class="w-full h-32 object-cover rounded-xl border border-slate-200">
                    </div>
                <?php endif; ?>
                <input type="file" name="gambar" accept="image/jpeg, image/png" <?php echo $galeri_edit ? '' : 'required'; ?> class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>

            <button type="submit" name="simpan_galeri" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-light transition-all flex items-center justify-center gap-1.5 font-heading uppercase tracking-wider text-[10px] cursor-pointer">
                <span class="material-symbols-outlined text-base">cloud_upload</span> 
                <?php echo $galeri_edit ? 'Simpan Perubahan' : 'Terbitkan Sekarang'; ?>
            </button>
        </form>
    </div>

    <div class="w-full lg:w-2/3 bg-white rounded-2xl border border-slate-100 shadow-editorial overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-heading text-xs font-bold text-primary uppercase tracking-wider">Arsip Berita & Galeri Terbit</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 font-heading text-[10px] font-bold uppercase tracking-wider text-text-muted border-b border-slate-100">
                        <th class="p-4 pl-6 w-32">Foto</th>
                        <th class="p-4">Detail Kegiatan Berita</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 pr-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-light divide-y divide-slate-100">
                    <?php
                    // Ambil gabungan tabel berita dengan tabel kategori menggunakan INNER JOIN
                    $query_berita = mysqli_query($koneksi, "
                        SELECT b.*, k.nama_kategori 
                        FROM berita b 
                        INNER JOIN kategori k ON b.id_kategori = k.id_kategori 
                        ORDER BY b.id_berita DESC
                    ");
                    
                    if ($query_berita && mysqli_num_rows($query_berita) > 0) {
                        while ($row = mysqli_fetch_assoc($query_berita)) {
                            $path_img = (!empty($row['gambar']) && file_exists(__DIR__ . "/uploads/berita/" . $row['gambar'])) ? "uploads/berita/" . $row['gambar'] : "https://placehold.co/600x400/E2E8F0/475569?text=Gambar+Kosong";
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <img src="<?php echo $path_img; ?>" alt="Berita" class="w-24 h-16 object-cover rounded-xl border border-slate-200 shadow-sm">
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="font-semibold text-primary text-sm leading-snug"><?php echo htmlspecialchars($row['judul_berita']); ?></div>
                                    <div class="text-[10px] text-text-muted font-mono mt-0.5">Rilis: <?php echo date("d M Y H:i", strtotime($row['tanggal_berita'])); ?></div>
                                </td>
                                <td class="p-4 align-middle">
                                    <span class="px-2 py-0.5 text-[9px] font-bold font-heading rounded bg-slate-100 text-slate-600 uppercase tracking-wider">
                                        <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                    </span>
                                </td>
                                <td class="p-4 pr-6 align-middle text-center whitespace-nowrap">
                                    <a href="dashboard.php?page=datagaleri&aksi=edit&id=<?php echo $row['id_berita']; ?>" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors mr-1">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <a href="dashboard.php?page=datagaleri&aksi=hapus&id=<?php echo $row['id_berita']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus berita/galeri ini beserta file fotonya secara permanen?');"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 bg-red-50 hover:bg-red-100 transition-colors">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" class="p-8 text-center text-text-muted font-light italic">Belum ada data berita atau galeri yang dimasukkan.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>