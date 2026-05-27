<?php
$conn = mysqli_connect("localhost", "root", "kelompok2web.", "pbokelompok2");
// Catatan: Jika root kamu pakai password (seperti di kode pertamamu 'kelompok2web.'), isi di antara tanda kutip kosong di atas.

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>  