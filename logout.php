<?php
// 1. Membuka akses kendali sesi yang sedang berjalan
session_start();

// 2. Mengosongkan seluruh variabel $_SESSION yang terdaftar
session_unset();

// 3. Menghancurkan paket sesi aktif secara total di sisi server
session_destroy();

// 4. Melempar kembali pengguna ke gerbang login dengan membawa pesan penanda sukses
header("Location: login.php?pesan=logout");
exit();
?>