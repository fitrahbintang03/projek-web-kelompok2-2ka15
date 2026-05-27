<?php
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

if (!file_exists('koneksi.php')) {
    echo json_encode(['ada_dekat' => false, 'error' => 'File koneksi.php tidak ditemukan!']);
    exit();
}

include 'koneksi.php';

if (!$conn) {
    echo json_encode(['ada_dekat' => false, 'error' => 'Koneksi database gagal!']);
    exit();
}

if (!isset($_SESSION['username'])) {
    echo json_encode(['ada_dekat' => false, 'pesan' => 'Belum login']);
    exit();
}

// AMBIL ID USER DARI SESSION
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;

/* QUERY YANG SUDAH DIFILTER BERDASARKAN ID_USER: 
   Mengecek tugas yang belum dikumpulkan oleh user yang sedang login saja.
*/
$query = "SELECT tugas.judul_tugas, mata_kuliah.nama_matkul, tugas.deadline 
          FROM tugas 
          JOIN mata_kuliah ON tugas.id_matkul = mata_kuliah.id_matkul 
          WHERE tugas.id_tugas NOT IN (
              SELECT id_tugas FROM pengumpulan WHERE id_user = '$id_user'
          )
          AND DATEDIFF(tugas.deadline, NOW()) <= 3 
          AND tugas.deadline >= NOW()
          ORDER BY tugas.deadline ASC LIMIT 1";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    
    // Hitung sisa hari secara real-time
    $tgl_deadline = new DateTime($data['deadline']);
    $tgl_sekarang = new DateTime();
    $selisih = $tgl_sekarang->diff($tgl_deadline)->days;

    echo json_encode([
        'ada_dekat' => true,
        'matkul' => $data['nama_matkul'],
        'judul' => $data['judul_tugas'],
        'sisa_hari' => $selisih
    ]);
} else {
    echo json_encode(['ada_dekat' => false]);
}
?>