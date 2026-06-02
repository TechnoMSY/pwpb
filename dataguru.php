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
// AMBIL DATA GURU UNTUK MODE EDIT
// ==========================================
$guru_edit = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_edit = intval($_GET['id']);
    $stmt_fetch = mysqli_prepare($koneksi, "
        SELECT u.id_user, u.nama, u.username, u.email, g.nip, g.mapel, g.jabatan, g.foto 
        FROM user u 
        INNER JOIN guru g ON u.id_user = g.id_user 
        WHERE u.id_user = ? AND u.level = 'guru'
    ");
    mysqli_stmt_bind_param($stmt_fetch, "i", $id_edit);
    mysqli_stmt_execute($stmt_fetch);
    $res_edit = mysqli_stmt_get_result($stmt_fetch);
    $guru_edit = mysqli_fetch_assoc($res_edit);
    mysqli_stmt_close($stmt_fetch);
}

// ==========================================
// 1. PROSES TAMBAH / UPDATE GURU
// ==========================================
if (isset($_POST['simpan_guru'])) {
    $mode     = $_POST['mode']; // 'tambah' atau 'edit'
    $id_user  = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
    
    $nama     = substr(trim($_POST['nama']), 0, 100);
    $username = substr(trim($_POST['username']), 0, 50);
    $email    = substr(trim($_POST['email']), 0, 100);
    $password = $_POST['password'];
    $nip      = !empty(trim($_POST['nip'])) ? substr(trim($_POST['nip']), 0, 30) : null;
    $mapel    = substr(trim($_POST['mapel']), 0, 100);
    $jabatan  = substr(trim($_POST['jabatan']), 0, 50);
    
    if (empty($nama) || empty($username) || empty($email) || ($mode == 'tambah' && empty($password))) {
        $pesan_error = "Semua kolom utama wajib diisi.";
    } else {
        if ($mode == 'edit') {
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id_user FROM user WHERE (username=? OR email=?) AND id_user != ?");
            mysqli_stmt_bind_param($stmt_cek, "ssi", $username, $email, $id_user);
        } else {
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id_user FROM user WHERE username=? OR email=?");
            mysqli_stmt_bind_param($stmt_cek, "ss", $username, $email);
        }
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        $user_exists = mysqli_stmt_num_rows($stmt_cek) > 0;
        mysqli_stmt_close($stmt_cek);

        $nip_exists = false;
        if (!empty($nip)) {
            if ($mode == 'edit') {
                $stmt_nip = mysqli_prepare($koneksi, "SELECT id_guru FROM guru WHERE nip=? AND id_user != ?");
                mysqli_stmt_bind_param($stmt_nip, "si", $nip, $id_user);
            } else {
                $stmt_nip = mysqli_prepare($koneksi, "SELECT id_guru FROM guru WHERE nip=?");
                mysqli_stmt_bind_param($stmt_nip, "s", $nip);
            }
            mysqli_stmt_execute($stmt_nip);
            mysqli_stmt_store_result($stmt_nip);
            if (mysqli_stmt_num_rows($stmt_nip) > 0) { $nip_exists = true; }
            mysqli_stmt_close($stmt_nip);
        }

        if ($user_exists) {
            $pesan_error = "Username atau Email sudah terdaftar.";
        } elseif ($nip_exists) {
            $pesan_error = "NIP sudah digunakan oleh pengajar lain.";
        } else {
            $foto_sekarang = "default.jpg";
            if ($mode == 'edit') {
                $stmt_img = mysqli_prepare($koneksi, "SELECT foto FROM guru WHERE id_user = ?");
                mysqli_stmt_bind_param($stmt_img, "i", $id_user);
                mysqli_stmt_execute($stmt_img);
                mysqli_stmt_bind_result($stmt_img, $foto_sekarang);
                mysqli_stmt_fetch($stmt_img);
                mysqli_stmt_close($stmt_img);
            }

            $nama_foto_db = ($mode == 'edit') ? $foto_sekarang : "default.jpg"; 
            $upload_ok = true;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['foto']['tmp_name'];
                $file_name = $_FILES['foto']['name'];
                $file_size = $_FILES['foto']['size'];
                
                $info      = getimagesize($file_tmp);
                $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
                
                if ($file_size > 2 * 1024 * 1024) {
                    $pesan_error = "Ukuran foto terlalu besar. Maksimal 2MB.";
                    $upload_ok = false;
                } elseif (!$info || !in_array($info[2], $allowed_types)) {
                    $pesan_error = "Format berkas tidak valid. Hanya JPG/PNG yang diizinkan.";
                    $upload_ok = false;
                } else {
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $nama_foto_db = "guru_" . bin2hex(random_bytes(8)) . "." . $ext;
                    $target_dir = __DIR__ . "/uploads/guru/";
                    
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, $target_dir . $nama_foto_db)) {
                        if ($mode == 'edit' && $foto_sekarang && $foto_sekarang !== 'default.jpg') {
                            @unlink($target_dir . $foto_sekarang);
                        }
                    } else {
                        $pesan_error = "Gagal mengunggah foto ke server berkas.";
                        $upload_ok = false;
                    }
                }
            }

            if ($upload_ok) {
                mysqli_begin_transaction($koneksi);
                try {
                    if ($mode == 'tambah') {
                        $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                        $level = 'guru';

                        $stmt_user = mysqli_prepare($koneksi, "INSERT INTO user (username, password, nama, email, level) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt_user, "sssss", $username, $password_hashed, $nama, $email, $level);
                        mysqli_stmt_execute($stmt_user);
                        $id_user_baru = mysqli_insert_id($koneksi);
                        mysqli_stmt_close($stmt_user);

                        $stmt_guru = mysqli_prepare($koneksi, "INSERT INTO guru (id_user, nip, jabatan, mapel, foto) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt_guru, "issss", $id_user_baru, $nip, $jabatan, $mapel, $nama_foto_db);
                        mysqli_stmt_execute($stmt_guru);
                        mysqli_stmt_close($stmt_guru);

                        $pesan_sukses = "Data guru <strong>" . htmlspecialchars($nama) . "</strong> berhasil disimpan.";
                    } elseif ($mode == 'edit') {
                        if (!empty($password)) {
                            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                            $stmt_user = mysqli_prepare($koneksi, "UPDATE user SET username=?, password=?, nama=?, email=? WHERE id_user=?");
                            mysqli_stmt_bind_param($stmt_user, "ssssi", $username, $password_hashed, $nama, $email, $id_user);
                        } else {
                            $stmt_user = mysqli_prepare($koneksi, "UPDATE user SET username=?, nama=?, email=? WHERE id_user=?");
                            mysqli_stmt_bind_param($stmt_user, "sssi", $username, $nama, $email, $id_user);
                        }
                        mysqli_stmt_execute($stmt_user);
                        mysqli_stmt_close($stmt_user);

                        $stmt_guru = mysqli_prepare($koneksi, "UPDATE guru SET nip=?, jabatan=?, mapel=?, foto=? WHERE id_user=?");
                        mysqli_stmt_bind_param($stmt_guru, "ssssi", $nip, $jabatan, $mapel, $nama_foto_db, $id_user);
                        mysqli_stmt_execute($stmt_guru);
                        mysqli_stmt_close($stmt_guru);

                        $pesan_sukses = "Data guru <strong>" . htmlspecialchars($nama) . "</strong> berhasil diperbarui.";
                        $guru_edit = null;
                    }
                    mysqli_commit($koneksi);
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $pesan_error = "Gagal menyimpan data: " . $e->getMessage();
                }
            }
        }
    }
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    $stmt_img = mysqli_prepare($koneksi, "SELECT foto FROM guru WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id_hapus);
    mysqli_stmt_execute($stmt_img);
    mysqli_stmt_bind_result($stmt_img, $foto_lama);
    mysqli_stmt_fetch($stmt_img);
    mysqli_stmt_close($stmt_img);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM user WHERE id_user=$id_hapus AND level='guru'");
    if ($query_hapus) {
        if ($foto_lama && $foto_lama !== 'default.jpg') {
            @unlink(__DIR__ . "/uploads/guru/" . $foto_lama);
        }
        $pesan_sukses = "Data pengajar berhasil dihapus dari sistem.";
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
                <span class="text-[10px] font-bold tracking-widest text-accent uppercase font-heading">Manajemen Staf</span>
                <h2 class="font-heading text-base font-bold text-primary mt-0.5">
                    <?php echo $guru_edit ? 'Ubah Data Pengajar' : 'Tambah Pengajar'; ?>
                </h2>
            </div>
            <?php if ($guru_edit): ?>
                <a href="dashboard.php?page=dataguru" class="text-[10px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-medium hover:bg-slate-200 transition-colors">Batal</a>
            <?php endif; ?>
        </div>

        <form action="dashboard.php?page=dataguru" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" name="mode" value="<?php echo $guru_edit ? 'edit' : 'tambah'; ?>">
            <?php if ($guru_edit): ?>
                <input type="hidden" name="id_user" value="<?php echo $guru_edit['id_user']; ?>">
            <?php endif; ?>

            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Nama Lengkap</label>
                <input type="text" name="nama" maxlength="100" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['nama']) : ''; ?>" placeholder="Ahmad Subarjo, S.Pd." required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">NIP (Boleh Kosong)</label>
                <input type="text" name="nip" maxlength="30" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['nip'] ?? '') : ''; ?>" placeholder="19820310..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary font-mono">
            </div>
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Mata Pelajaran</label>
                <input type="text" name="mapel" maxlength="100" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['mapel']) : ''; ?>" placeholder="Fikih / Matematika" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Jabatan</label>
                <input type="text" name="jabatan" maxlength="50" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['jabatan']) : 'Guru Mapel'; ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>
            
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Foto Profil (JPG/PNG, Maks 2MB)</label>
                <?php if ($guru_edit && !empty($guru_edit['foto'])): ?>
                    <div class="flex items-center gap-3 mb-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <img src="uploads/guru/<?php echo $guru_edit['foto']; ?>" class="w-16 h-16 object-cover rounded-xl border border-slate-200" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($guru_edit['nama']); ?>'">
                        <span class="text-[10px] text-text-muted truncate max-w-[140px]"><?php echo htmlspecialchars($guru_edit['foto']); ?></span>
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/jpeg, image/png" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>

            <div class="border-t border-slate-100 pt-3">
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Username Akun</label>
                <input type="text" name="username" maxlength="50" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['username']) : ''; ?>" placeholder="ahmad123" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary font-mono">
            </div>
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Email</label>
                <input type="email" name="email" maxlength="100" value="<?php echo $guru_edit ? htmlspecialchars($guru_edit['email']) : ''; ?>" placeholder="ahmad@sekolah.sch.id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>
            <div>
                <label class="block font-medium text-primary mb-1.5 font-heading uppercase tracking-wider text-[10px]">Password Akun <?php echo $guru_edit ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                <input type="password" name="password" placeholder="******" <?php echo $guru_edit ? '' : 'required'; ?> class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-accent text-primary">
            </div>

            <button type="submit" name="simpan_guru" class="w-full bg-accent text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-all flex items-center justify-center gap-1.5 font-heading uppercase tracking-wider text-[11px] cursor-pointer">
                <span class="material-symbols-outlined text-base"><?php echo $guru_edit ? 'save' : 'person_add'; ?></span> 
                <?php echo $guru_edit ? 'Simpan Perubahan' : 'Simpan Data Guru'; ?>
            </button>
        </form>
    </div>

    <div class="w-full lg:w-2/3 bg-white rounded-2xl border border-slate-100 shadow-editorial overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-heading text-xs font-bold text-primary uppercase tracking-wider">Database Relasional Guru</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 font-heading text-[10px] font-bold uppercase tracking-wider text-text-muted border-b border-slate-100">
                        <th class="p-4 pl-6 w-32">Foto</th>
                        <th class="p-4">Nama / NIP</th>
                        <th class="p-4">Tugas Mengajar</th>
                        <th class="p-4">Kontak / Email</th>
                        <th class="p-4 pr-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-light divide-y divide-slate-100">
                    <?php
                    $query_guru = mysqli_query($koneksi, "
                        SELECT u.id_user, u.nama, u.email, g.nip, g.mapel, g.jabatan, g.foto 
                        FROM user u 
                        INNER JOIN guru g ON u.id_user = g.id_user 
                        WHERE u.level = 'guru' 
                        ORDER BY g.id_guru DESC
                    ");
                    
                    if ($query_guru && mysqli_num_rows($query_guru) > 0) {
                        while ($row = mysqli_fetch_assoc($query_guru)) {
                            $path_foto = (!empty($row['foto']) && file_exists(__DIR__ . "/uploads/guru/" . $row['foto'])) ? "uploads/guru/" . $row['foto'] : "https://ui-avatars.com/api/?name=" . urlencode($row['nama']) . "&background=E2E8F0&color=475569";
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 pl-6">
                                    <img src="<?php echo $path_foto; ?>" alt="Foto Guru" class="w-24 h-24 object-cover rounded-xl border border-slate-200 shadow-sm transition-transform hover:scale-105 duration-200">
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="font-medium text-primary text-sm"><?php echo htmlspecialchars($row['nama']); ?></div>
                                    <div class="text-[10px] text-text-muted font-mono mt-0.5"><?php echo $row['nip'] ? htmlspecialchars($row['nip']) : '-'; ?></div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="font-medium text-accent"><?php echo htmlspecialchars($row['mapel']); ?></div>
                                    <div class="text-[10px] text-text-muted mt-0.5"><?php echo htmlspecialchars($row['jabatan']); ?></div>
                                </td>
                                <td class="p-4 align-middle text-text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="p-4 pr-6 align-middle text-center whitespace-nowrap">
                                    <a href="dashboard.php?page=dataguru&aksi=edit&id=<?php echo $row['id_user']; ?>" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors mr-1" title="Ubah Data">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <a href="dashboard.php?page=dataguru&aksi=hapus&id=<?php echo $row['id_user']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus pengajar ini? Data login, profil, dan berkas foto akan dihapus permanen.');"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 bg-red-50 hover:bg-red-100 transition-colors" title="Hapus Data">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5" class="p-8 text-center text-text-muted font-light italic">Belum ada data guru pengajar di database.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>