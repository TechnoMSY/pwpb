<?php
// Proteksi halaman agar tidak bisa diakses langsung tanpa melalui dashboard.php
if (!isset($page)) {
    die("Direct access strictly prohibited.");
}

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
// PROSES UPDATE STATUS PENDAFTARAN
// ==========================================
if (isset($_POST['update_status'])) {
    $id_pendaftaran = intval($_POST['id_pendaftaran']);
    $status_baru = $_POST['status_verifikasi']; // Disesuaikan dengan Enum di Database: 'Pending', 'Diterima', 'Ditolak'

    if (in_array($status_baru, ['Pending', 'Diterima', 'Ditolak'])) {
        $stmt_update = mysqli_prepare($koneksi, "UPDATE pendaftaran SET status_verifikasi = ? WHERE id_pendaftaran = ?");
        mysqli_stmt_bind_param($stmt_update, "si", $status_baru, $id_pendaftaran);

        if (mysqli_stmt_execute($stmt_update)) {
            $pesan_sukses = "Status pendaftaran siswa berhasil diperbarui menjadi <strong>$status_baru</strong>.";
        } else {
            $pesan_error = "Gagal memperbarui status: " . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt_update);
    }
}

// ==========================================
// PROSES HAPUS DATA PENDAFTARAN
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM pendaftaran WHERE id_pendaftaran = $id_hapus");
    if ($query_hapus) {
        $pesan_sukses = "Data pendaftaran siswa berhasil dihapus dari sistem.";
    } else {
        $pesan_error = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<div style="font-family: 'Plus Jakarta Sans', sans-serif;">

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

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <?php
        $res_all = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran");
        $t_all = mysqli_fetch_assoc($res_all)['total'] ?? 0;

        $res_acc = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran WHERE status_verifikasi='Diterima'");
        $t_acc = mysqli_fetch_assoc($res_acc)['total'] ?? 0;

        $res_pen = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran WHERE status_verifikasi='Pending'");
        $t_pen = mysqli_fetch_assoc($res_pen)['total'] ?? 0;
        ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-slate-50 text-slate-700 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-sm">group</span></div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400">Total Pendaftar</p>
                <h3 class="text-lg font-bold text-slate-800"><?php echo $t_all; ?> <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-sm">hourglass_empty</span></div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-wider text-blue-500">Belum Verifikasi</p>
                <h3 class="text-lg font-bold text-blue-600"><?php echo $t_pen; ?> <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><span class="material-symbols-outlined text-sm">verified</span></div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-wider text-emerald-600">Lolos Seleksi</p>
                <h3 class="text-lg font-bold text-emerald-600"><?php echo $t_acc; ?> <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
            </div>
        </div>
    </div>

    <div class="w-full bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Registrasi Peserta Didik Baru (PPDB)</h2>
                <p class="text-[11px] text-slate-400 font-normal mt-0.5">Kelola verifikasi berkas formulir kelulusan calon pendaftar.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="p-4 pl-6 w-12 text-center">No</th>
                        <th class="p-4">Identitas Siswa</th>
                        <th class="p-4">Asal Sekolah</th>
                        <th class="p-4">Kontak Wali</th>
                        <th class="p-4 text-center w-44">Status Validasi</th>
                        <th class="p-4 pr-6 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-normal divide-y divide-slate-100">
                    <?php
                    $query_ppdb = mysqli_query($koneksi, "SELECT * FROM pendaftaran ORDER BY id_pendaftaran DESC");

                    if ($query_ppdb && mysqli_num_rows($query_ppdb) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($query_ppdb)) {
                            ?>
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-4 pl-6 text-center font-mono font-medium text-slate-400"><?php echo $no++; ?></td>
                                <td class="p-4 align-middle">
                                    <div class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: <?php echo htmlspecialchars($row['nisn']); ?></div>
                                </td>
                                <td class="p-4 align-middle text-slate-700 font-semibold">
                                    <?php echo htmlspecialchars($row['asal_sekolah']); ?>
                                </td>
                                <td class="p-4 align-middle">
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['no_hp_ortu']); ?>" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold">
                                        <span class="material-symbols-outlined text-xs">call</span>
                                        <?php echo htmlspecialchars($row['no_hp_ortu']); ?>
                                    </a>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <form action="dashboard.php?page=pendaftaran" method="POST" class="flex items-center justify-center">
                                        <input type="hidden" name="id_pendaftaran" value="<?php echo $row['id_pendaftaran']; ?>">
                                        <select name="status_verifikasi" onchange="this.form.submit()" class="text-[11px] font-bold py-1.5 px-3 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 text-slate-700 cursor-pointer transition-all">
                                            <option value="Pending" <?php echo $row['status_verifikasi'] == 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                            <option value="Diterima" <?php echo $row['status_verifikasi'] == 'Diterima' ? 'selected' : ''; ?>>✅ Diterima</option>
                                            <option value="Ditolak" <?php echo $row['status_verifikasi'] == 'Ditolak' ? 'selected' : ''; ?>>❌ Ditolak</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="p-4 pr-6 align-middle text-center">
                                    <a href="dashboard.php?page=pendaftaran&aksi=hapus&id=<?php echo $row['id_pendaftaran']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas data pendaftaran siswa ini?')" class="text-red-500 hover:text-red-700 inline-flex items-center justify-center p-2 rounded-xl hover:bg-red-50 transition-colors" title="Hapus Data">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="p-8 text-center text-slate-400 italic font-normal">Belum ada berkas formulir calon siswa baru yang masuk.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>