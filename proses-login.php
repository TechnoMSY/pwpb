<?php
// Memulai session PHP
session_start();

// Menghubungkan ke file koneksi database utama
require_once 'koneksi.php';

// Proses logika autentikasi login ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=" . urlencode("Username dan password tidak boleh kosong."));
        exit();
    }

    try {
        // Mengambil data user berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch();

        if ($user) {
            // PERBAIKAN: Mengubah verifikasi password agar mencocokkan dengan enkripsi MD5
            if (md5($password) === $user['password']) {
                
                // Menyimpan data kredensial ke dalam Session jika sukses
                $_SESSION['status_login']  = true;
                $_SESSION['id_user']       = $user['id_user'];
                $_SESSION['user_admin']    = $user['nama'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['level']         = $user['level'];
                $_SESSION['kategori_guru'] = $user['kategori_guru'];
                
                // Dialokasikan ke halaman dashboard.php yang sejajar
                header("Location: dashboard.php");
                exit();
                
            } else {
                header("Location: login.php?error=" . urlencode("Kata sandi yang Anda masukkan salah."));
                exit();
            }
        } else {
            header("Location: login.php?error=" . urlencode("Akun username tidak terdaftar di sistem."));
            exit();
        }

    } catch (PDOException $e) {
        header("Location: login.php?error=" . urlencode("Terjadi kesalahan sistem: " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: login.php");
    exit();
}