<?php
// 1. JALANKAN SESSION
session_start();
include 'koneksi.php';

// 2. AMBIL ID USER DARI SESSION
if (!isset($_SESSION['id_user'])) {
    header("Location: login_html.php");
    exit();
}
$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tugas = $_POST['id_tugas'];
    $catatan  = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // VALIDASI DEADLINE
    $query_cek_deadline = "SELECT deadline, judul_tugas FROM tugas WHERE id_tugas = '$id_tugas'";
    $result_deadline = mysqli_query($conn, $query_cek_deadline);
    
    if ($result_deadline && mysqli_num_rows($result_deadline) > 0) {
        $data_tugas = mysqli_fetch_assoc($result_deadline);
        if (time() > strtotime($data_tugas['deadline'])) {
            $_SESSION['error'] = "Pengumpulan ditolak! Anda sudah melewati tenggat waktu.";
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // VALIDASI DOUBLE SUBMIT
    $query_cek_double = "SELECT id_pengumpulan FROM pengumpulan WHERE id_tugas = '$id_tugas' AND id_user = '$id_user'";
    $result_double = mysqli_query($conn, $query_cek_double);

    if (mysqli_num_rows($result_double) > 0) {
        $_SESSION['error'] = "Gagal! Anda sudah mengumpulkan tugas ini sebelumnya.";
        header("Location: dashboard.php");
        exit();
    }

    // PROSES UPLOAD FILE
    $nama_file   = $_FILES['file_tugas']['name'];
    $tmp_file    = $_FILES['file_tugas']['tmp_name'];
    $error_file  = $_FILES['file_tugas']['error'];
    
    if ($error_file === 0) {
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_file_baru = uniqid() . "." . $ekstensi;
        $folder_tujuan = "uploads/" . $nama_file_baru;
        
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        if (move_uploaded_file($tmp_file, $folder_tujuan)) {
            
            // INSERT KE DATABASE PENGUMPULAN
            $query = "INSERT INTO pengumpulan (id_tugas, id_user, nama_file, catatan, waktu_kumpul) 
                      VALUES ('$id_tugas', '$id_user', '$nama_file_baru', '$catatan', NOW())";
            $simpan = mysqli_query($conn, $query);
            
            // ... (bagian kode sebelumnya tetap sama)

            if ($simpan) {
                // =================================================================
                // BAGIAN CATAT KE TABEL AKTIVITAS
                // =================================================================
                $judul = $data_tugas['judul_tugas'];
                $aksi = "Mengumpulkan tugas: " . $judul;
                // Pastikan nama kolom 'aktivitas' sesuai dengan struktur tabel Anda
                $query_log = "INSERT INTO aktivitas (username, aktivitas, waktu) VALUES ('{$_SESSION['username']}', '$aksi', NOW())";
                mysqli_query($conn, $query_log);
                
                // =================================================================
                // MODIFIKASI: Arahkan ke page tugas dan simpan session untuk notif
                // =================================================================
               // ... (dalam blok if($simpan))
                $_SESSION['notif_sukses'] = "Tugas berhasil dikumpulkan!";
                header("Location: dashboard.php?page=tugas"); 
                exit();

                // ... (dalam blok error)
                // Tambahkan ?page=tugas agar user tetap di tab tugas saat error
                $_SESSION['error'] = "Pesan error...";
                header("Location: dashboard.php?page=tugas"); 
                exit();
            }
        } else {
            $_SESSION['error'] = "Gagal mengunggah file ke server.";
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Terjadi kesalahan pada file.";
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>