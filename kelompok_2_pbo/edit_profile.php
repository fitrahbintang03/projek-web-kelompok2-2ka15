<?php
session_start();
// 1. Hubungkan ke database Anda (sesuaikan dengan file koneksi Anda)
include 'koneksi.php'; 

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit_update'])) {
    $user_id = $_SESSION['user_id'];
    
    // Ambil data dari input form dan bersihkan untuk keamanan
    $email_baru = mysqli_real_escape_string($koneksi, $_POST['email']);
    $npm_baru = mysqli_real_escape_string($koneksi, $_POST['npm']);
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama']); // jika ingin mengubah nama juga

    // 2. Query SQL untuk mengupdate database users
    $query = "UPDATE users SET email = '$email_baru', npm = '$npm_baru', nama = '$nama_baru' WHERE id = '$user_id'";
    
    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, update juga data session agar tampilan langsung berubah
        $_SESSION['email'] = $email_baru;
        $_SESSION['npm'] = $npm_baru;
        $_SESSION['nama'] = $nama_baru;
        
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profile.php';</script>";
    } else {
        echo "Gagal memperbarui profil: " . mysqli_error($koneksi);
    }
}
?>