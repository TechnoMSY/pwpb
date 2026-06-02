<?php
// 1. PENGATURAN KONFIGURASI DATABASE
$db_host = "localhost";
$db_user = "root";      // Username default XAMPP/Laragon biasanya 'root'
$db_pass = "";          // Password default biasanya dikosongkan ''
$db_name = "mts";       // Nama database Anda

try {
    // 2. MEMBUAT KONEKSI MENGGUNAKAN DRIVER PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 3. TAMBAHAN: MEMBUAT KONEKSI DRIVER MYSQLI (Untuk dataguru.php)
    $koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (!$koneksi) {
        throw new Exception("Gagal membuat koneksi MySQLi: " . mysqli_connect_error());
    }

} catch (Exception $e) {
    // 4. PENANGANAN JIKA KONEKSI GAGAL
    die("Sistem gagal terhubung ke database. Error: " . $e->getMessage());
}
?>