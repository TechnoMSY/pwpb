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

// ==========================================
// AMBIL DATA KATEGORI UNTUK MODE EDIT
// ==========================================
$kategori_edit = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_edit = intval($_GET['id']);
    $stmt_fetch = mysqli_prepare($koneksi, "SELECT id_kategori, nama_kategori FROM kategori WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt_fetch, "i", $id_edit);
    mysqli_stmt_execute($stmt_fetch);
    $res_edit = mysqli_stmt_get_result($stmt_fetch);
    $kategori_edit = mysqli_fetch_assoc($res_edit);
    mysqli_stmt_close($stmt_fetch);
}

// ==========================================
// PROSES SIMPAN (TAMBAH / UPDATE) KATEGORI
// ==========================================
if (isset($_POST['simpan_kategori'])) {
    $mode          = $_POST['mode']; // 'tambah' atau 'edit'
    $id_kategori   = isset($_POST['id_kategori']) ? intval($_POST['id_kategori']) : 0;
    $nama_kategori = trim($_POST['nama_kategori']);

    if (empty($nama_kategori)) {
        $pesan_error = "Nama kategori tidak boleh kosong.";
    } else {
        if ($mode == 'tambah') {
            // Cek apakah nama kategori sudah ada sebelumnya
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
            mysqli_stmt_bind_param($stmt_cek, "s", $nama_kategori);
            mysqli_stmt_execute($stmt_cek);
            mysqli_stmt_store_result($stmt_cek);
            
            if (mysqli_stmt_num_rows($stmt_cek) > 0) {
                $pesan_error = "Kategori dengan nama tersebut sudah ada.";
            } else {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama_kategori) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan_sukses = "Kategori baru berhasil ditambahkan.";
                } else {
                    $pesan_error = "Gagal menyimpan data: " . mysqli_error($koneksi);
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($stmt_cek);
            
        } elseif ($mode == 'edit') {
            $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
            mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $id_kategori);
            if (mysqli_stmt_execute($stmt)) {
                $pesan_sukses = "Nama kategori berhasil diperbarui.";
                $kategori_edit = null; // Reset mode edit
            } else {
                $pesan_error = "Gagal memperbarui data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ==========================================
// PROSES HAPUS KATEGORI
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    // Sesuai dengan batasan ON DELETE CASCADE di database Anda, 
    // jika kategori dihapus, maka semua berita di bawah kategori tersebut akan ikut terhapus.
    $query_hapus = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = $id_hapus");
    if ($query_hapus) {
        $pesan_sukses = "Kategori berhasil dihapus beserta seluruh berita/galeri terkait di dalamnya.";
    } else {
        $pesan_error = "Gagal menghapus kategori: " . mysqli_error($koneksi);
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
                <span class="text-[10px] font-bold tracking-widest text-accent uppercase font-heading">Taksonomi Konten</span>
                <h2 class="font-heading text-base font-bold text-primary mt-0.5">
                    <?php echo $kategori_edit ? 'Ubah Kategori' : 'Tambah Kategori'; ?>
                </h2>
            </div>
            <?php if ($kategori_edit): ?>
                <a href="dashboard.php?page=kategori" class="text-[10px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-medium hover:bg-slate-200 transition-colors">Batal</a>
            <?php endif; ?>
        </div>

        <form action="dashboard.php?page=kategori" method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="mode" value="<?php echo $kategori_edit ? 'edit' : 'tambah'; ?>">
            <?php if ($kategori_edit): ?>
                <input type="hidden" name="id_kategori" value="<?php echo $kategori_edit['id_kategori']; ?>">
            <?php endif; ?>

            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Nama Kategori</label>
                <input type="text" name="nama_kategori" value="<?php echo $kategori_edit ? htmlspecialchars($kategori_edit['nama_kategori']) : ''; ?>" placeholder="Contoh: Akademik, Ekstrakurikuler, Event" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
                <p class="text-[10px] text-text-muted mt-1 font-light">Gunakan nama kategori yang umum digunakan untuk mengelompokkan dokumentasi galeri dan berita.</p>
            </div>

            <button type="submit" name="simpan_kategori" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-light transition-all flex items-center justify-center gap-1.5 font-heading uppercase tracking-wider text-[10px] cursor-pointer">
                <span class="material-symbols-outlined text-base">folder_managed</span> 
                <?php echo $kategori_edit ? 'Simpan Perubahan' : 'Simpan Kategori'; ?>
            </button>
        </form>
    </div>

    <div class="w-full lg:w-2/3 bg-white rounded-2xl border border-slate-100 shadow-editorial overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-heading text-xs font-bold text-primary uppercase tracking-wider">Daftar Kategori Terdaftar</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 font-heading text-[10px] font-bold uppercase tracking-wider text-text-muted border-b border-slate-100">
                        <th class="p-4 pl-6 w-16 text-center">No</th>
                        <th class="p-4">Nama Kategori</th>
                        <th class="p-4 text-center w-40">Jumlah Berita/Galeri</th>
                        <th class="p-4 pr-6 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-light divide-y divide-slate-100">
                    <?php
                    // Query untuk mengambil kategori beserta jumlah berita terkait di dalamnya
                    $query_kategori = mysqli_query($koneksi, "
                        SELECT k.id_kategori, k.nama_kategori, COUNT(b.id_berita) AS total_berita 
                        FROM kategori k 
                        LEFT JOIN berita b ON k.id_kategori = b.id_kategori 
                        GROUP BY k.id_kategori 
                        ORDER BY k.nama_kategori ASC
                    ");
                    
                    if ($query_kategori && mysqli_num_rows($query_kategori) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($query_kategori)) {
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6 text-center font-mono font-medium text-text-muted"><?php echo $no++; ?></td>
                                <td class="p-4 align-middle font-semibold text-primary text-sm">
                                    <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <span class="px-2.5 py-1 text-[10px] font-mono font-bold rounded-full bg-slate-100 text-slate-600">
                                        <?php echo $row['total_berita']; ?> Post
                                    </span>
                                </td>
                                <td class="p-4 pr-6 align-middle text-center whitespace-nowrap">
                                    <a href="dashboard.php?page=kategori&aksi=edit&id=<?php echo $row['id_kategori']; ?>" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors mr-1" title="Ubah Nama">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <a href="dashboard.php?page=kategori&aksi=hapus&id=<?php echo $row['id_kategori']; ?>" 
                                       onclick="return confirm('PENTING: Menghapus kategori ini juga akan menghapus seluruh data Berita dan Galeri foto di dalamnya! Apakah Anda yakin?');"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 bg-red-50 hover:bg-red-100 transition-colors" title="Hapus Kategori">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" class="p-8 text-center text-text-muted font-light italic">Belum ada kategori yang dikonfigurasi.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>