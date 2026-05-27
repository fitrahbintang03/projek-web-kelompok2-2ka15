<?php
session_start();
$nama_user = isset($_SESSION['username']) ? $_SESSION['username'] : "Pengguna";

// 1. Simpan pesan sebelum semua data dihapus
$_SESSION['logout_success'] = "Sampai jumpa lagi, " . $nama_user . "! Terima kasih telah menggunakan Vclass.";

// 2. Hancurkan sesi
session_unset();
session_destroy();

// 3. MULAI SESI BARU HANYA UNTUK PESAN
session_start();
$_SESSION['logout_success'] = "Sampai jumpa lagi, " . $nama_user . "! Terima kasih telah menggunakan Vclass.";

header("Location: login_html.php");
exit();
?>