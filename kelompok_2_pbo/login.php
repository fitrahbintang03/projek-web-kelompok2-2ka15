<?php
// Wajib ada agar sistem ingat siapa yang sedang login saat pindah ke dashboard
session_start(); 

// 1. Hubungkan ke database pbokelompok2
$host     = "localhost";
$username = "root";
$password = "kelompok2web."; 
$database = "pbokelompok2";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 2. Jalankan logika Login jika tombol submit ditekan
if (isset($_POST['btn_submit'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    // Langsung cari username di database   
    $query_login = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
    
    // Jika username ditemukan
    if (mysqli_num_rows($query_login) > 0) {
        $data_user = mysqli_fetch_assoc($query_login);
        
        // Verifikasi kecocokan password
        if (password_verify($pass, $data_user['password'])) {
            // Simpan data user ke dalam sesi
            $_SESSION['username'] = $data_user['username'];
            
            // --- PERBAIKAN: Menyimpan ID user ke session agar dashboard bisa memfilter tugas ---
            $_SESSION['id_user'] = $data_user['id']; 

            // Simpan pesan sukses ke session
           $_SESSION['login_success'] = "Selamat datang kembali, " . $data_user['username'] . "!";
            
            // Pindah ke dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Jika password salah
            $_SESSION['error'] = "Password yang Anda masukkan salah!";
            header("Location: login_html.php");
            exit();
        }
    } else {
        // Jika username tidak ada di database
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: login_html.php");
        exit();
    }
}
?>