<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

// Menggunakan kolom created_at yang baru saja kita tambahkan
// Cek apakah ada tugas yang dibuat dalam 10 detik terakhir
$query = "SELECT COUNT(*) as total FROM tugas WHERE created_at >= NOW() - INTERVAL 10 SECOND";
$result = mysqli_query($conn, $query);

if ($result) {
    $data = mysqli_fetch_assoc($result);
    // Kirim response JSON yang bersih
    echo json_encode(['ada_baru' => ($data['total'] > 0)]);
} else {
    echo json_encode(['ada_baru' => false]);
}
exit();
?>