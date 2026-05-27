<?php 
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: login_html.php");
    exit();
}

include 'koneksi.php'; 

// Mengambil ID user dengan aman
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;

// Query 1: Daftar tugas utama
$query = "SELECT tugas.*, mata_kuliah.nama_matkul, 
                 (SELECT id_pengumpulan FROM pengumpulan 
                  WHERE pengumpulan.id_tugas = tugas.id_tugas 
                  AND pengumpulan.id_user = '$id_user' LIMIT 1) as id_pengumpulan
          FROM tugas 
          JOIN mata_kuliah ON tugas.id_matkul = mata_kuliah.id_matkul 
          ORDER BY deadline ASC";
$result_tugas = mysqli_query($conn, $query);

// Query 2: Tugas Mendatang
$query_mendatang = "SELECT tugas.*, mata_kuliah.nama_matkul 
                    FROM tugas 
                    JOIN mata_kuliah ON tugas.id_matkul = mata_kuliah.id_matkul 
                    WHERE tugas.deadline >= NOW() 
                    AND tugas.id_tugas NOT IN (SELECT id_tugas FROM pengumpulan WHERE id_user = '$id_user')
                    ORDER BY deadline ASC 
                    LIMIT 5";
$result_mendatang = mysqli_query($conn, $query_mendatang);

// Query 3: Deadline Penting
$query_penting = "SELECT tugas.*, mata_kuliah.nama_matkul 
                  FROM tugas 
                  JOIN mata_kuliah ON tugas.id_matkul = mata_kuliah.id_matkul 
                  WHERE tugas.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                  AND tugas.id_tugas NOT IN (SELECT id_tugas FROM pengumpulan WHERE id_user = '$id_user')
                  ORDER BY deadline ASC";
$result_penting = mysqli_query($conn, $query_penting);

// Statistik Indikator
$total_mendatang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE deadline >= NOW() AND id_tugas NOT IN (SELECT id_tugas FROM pengumpulan WHERE id_user = '$id_user')"))['total'];
$total_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengumpulan WHERE id_user = '$id_user'"))['total'];
$total_matkul = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mata_kuliah"))['total'];
$res_deadline = mysqli_fetch_assoc(mysqli_query($conn, "SELECT deadline FROM tugas WHERE deadline >= NOW() ORDER BY deadline ASC LIMIT 1"));
$deadline_terdekat = $res_deadline ? date('d M', strtotime($res_deadline['deadline'])) : "-";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>VclassV2ByKelompok2</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="desain.css?v=3">
</head>
<body>

<div id="modalDetail" class="modal-overlay">
    <div class="modal-content">
        <form action="proses_upload.php" method="POST" enctype="multipart/form-data">
            <h3>Detail Tugas</h3>

            <input type="hidden" id="dIdTugas" name="id_tugas">

            <p><b>Mata Kuliah:</b> <span id="dMatkul"></span></p>
            <p><b>Judul:</b> <span id="dJudul"></span></p>
            <p><b>Deadline:</b> <span id="dDeadline"></span></p>

            <h4>Upload Tugas</h4>
            <div class="upload-box">
                Klik atau drag file ke sini
                <br><br>
                <input type="file" name="file_tugas" required>
            </div>

            <textarea class="note" name="catatan" rows="4" placeholder="Catatan untuk dosen..."></textarea>
            <br><br>

            <button type="submit" class="btn-kumpul">Kumpulkan Tugas</button>
            <button type="button" onclick="closeModal()" class="btn-keluar">Keluar</button>
        </form>
    </div>
</div>      

<div class="container">
    <div class="sidebar">
        <ul class="menu">
            <li class="active" onclick="showPage('beranda', this)">🏠 Beranda</li>
            <li onclick="showPage('tugas', this)">📄 Tugas Saya</li>
            <li onclick="window.location.href='profile.php'">👤 Profil</li>
            <li onclick="window.location.href='logout.php'" style="color: #ff4d4d; font-weight: bold; margin-top: 30px; border-top: 1px solid rgba(108, 99, 255, 0.1); padding-top: 15px;">🚪 Keluar</li>
        </ul>
    </div>

    <div class="main">
        <div id="beranda" class="page active-page">
            <div class="header">
                <h2>Beranda Tugas</h2>
                <p>Hai, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</p>
            </div>

            <div class="top-cards">
                <div class="mini-card">📚<h1><?= $total_mendatang; ?></h1><small>Tugas Mendatang</small></div>
                <div class="mini-card">✅<h1><?= $total_selesai; ?></h1><small>Tugas Selesai</small></div>
                <div class="mini-card">🏫<h1><?= $total_matkul; ?></h1><small>Kelas Aktif</small></div>
                <div class="mini-card">⏰<h1><?= $deadline_terdekat; ?></h1><small>Deadline Terdekat</small></div>
            </div>

            <div class="search">
            <input type="text" id="searchInput" onkeyup="filterTugas()" placeholder="Cari Tugas...">
            </div>

            <div class="content">
                <div class="card left">
                    <h3>Tugas Mendatang Saya</h3>
                    
                    <?php if ($result_mendatang && mysqli_num_rows($result_mendatang) > 0): ?>
                        <?php while ($mendatang = mysqli_fetch_assoc($result_mendatang)): ?>
                            <div class="task">
                                <div>
                                    <b><?= htmlspecialchars($mendatang['nama_matkul']); ?></b><br>
                                    <?= htmlspecialchars($mendatang['judul_tugas']); ?><br>
                                    <small>Deadline: <?= date('d M Y', strtotime($mendatang['deadline'])); ?></small>
                                </div>
                                <input type="checkbox" disabled>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #8e8ca9; font-size: 13px; padding: 20px 0; text-align: center;">Tidak ada tugas mendatang. Bersantai sejenak! ✨</p>
                    <?php endif; ?>
                </div>

                <div class="card right">
                    <h3>Deadline Penting Minggu Ini</h3>
                    
                    <?php if ($result_penting && mysqli_num_rows($result_penting) > 0): ?>
                        <?php while ($penting = mysqli_fetch_assoc($result_penting)): ?>
                            <div class="task important">
                                <div>
                                    <b><?= htmlspecialchars($penting['nama_matkul']); ?></b><br>
                                    Jatuh Tempo: <?= date('d M Y', strtotime($penting['deadline'])); ?><br>
                                    <small style="color: #ff4d4d; font-weight: bold;">⚠️ MINGGU INI</small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #8e8ca9; font-size: 13px; padding: 20px 0; text-align: center;">Aman! Tidak ada deadline mendesak minggu ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="tugas" class="page">
            <h2>Tugas Saya</h2>
            <div class="card">
                <h3>Daftar Tugas</h3>
                
                <?php if ($result_tugas && mysqli_num_rows($result_tugas) > 0): ?>
                    <?php while ($tugas = mysqli_fetch_assoc($result_tugas)): ?>
                        <div class="task">
                            <div>
                                <b><?= htmlspecialchars($tugas['nama_matkul']); ?></b><br>
                                <?= htmlspecialchars($tugas['judul_tugas']); ?><br>
                                <small>Deadline: <?= date('d M Y', strtotime($tugas['deadline'])); ?></small>
                            </div>
                            
                            <div style="display:flex; align-items:center;">
                                <?php 
                                    $status_class = (strtotime($tugas['deadline']) < time()) ? 'terlambat' : 'akan-datang';
                                    $status_label = (strtotime($tugas['deadline']) < time()) ? 'Terlambat' : 'Akan Datang';
                                    
                                    $sudah_dikumpul = !empty($tugas['id_pengumpulan']);
                                ?>

                                <?php if ($sudah_dikumpul): ?>
                                    <div class="status" style="background-color: #e6f9ed; color: #1dd1a1;">Selesai</div>
                                    <button class="btn-kumpul" style="background: #e0e0e0; color: #a0a0a0; cubic-bezier(0.25, 0.8, 0.25, 1); cursor: not-allowed; box-shadow: none;" disabled>🔒 Terkunci</button>

                                <?php elseif ($status_class == 'terlambat'): ?>
                                    <div class="status <?= $status_class; ?>"><?= $status_label; ?></div>
                                    <button class="btn-kumpul" style="background: linear-gradient(135deg, #ff6b6b 0%, #dc3545 100%); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2); cursor: not-allowed;" disabled>Ditutup</button>

                                <?php else: ?>
                                    <div class="status <?= $status_class; ?>"><?= $status_label; ?></div>
                                    <button class="btn-kumpul" onclick="showDetail(
                                        '<?= $tugas['id_tugas']; ?>', 
                                        '<?= addslashes($tugas['nama_matkul']); ?>', 
                                        '<?= addslashes($tugas['judul_tugas']); ?>', 
                                        '<?= date('d M Y', strtotime($tugas['deadline'])); ?>'
                                    )">Kumpulkan</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #8e8ca9; padding: 20px;">Tidak ada daftar tugas saat ini.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="widgetBawah">
            <div class="section-box">
                <div class="section-header">
                    <h3>Pengumuman Terbaru</h3><small style="color: #6c63ff; font-weight: 600; cursor: pointer;">Lihat Semua</small>
                </div>
                <div class="activity-item">
                    <div><b>Perkuliahan Daring Minggu Depan</b><br><small>Perkuliahan online.</small></div>
                    <small style="color: #8e8ca9;">15 Okt 2024</small>
                </div>
            </div>
        </div>
    </div> 
</div> 

<script src="script.js?t=<?= time(); ?>"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetPage = urlParams.get('page');
    
    if (targetPage) {
        const menuItems = document.querySelectorAll('.sidebar .menu li');
        let targetElement = menuItems[0];
        
        if (targetPage === 'tugas') {
            targetElement = menuItems[1];
        }
        
        if (typeof showPage === "function") {
            showPage(targetPage, targetElement);
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['login_success'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Gunakan timeout agar script menunggu library selesai dimuat
        setTimeout(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?= $_SESSION['login_success']; ?>',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.style.zIndex = '999999'; // Memaksa muncul di paling depan
                }
            });
        }, 500); // Tunggu 0.5 detik
    </script>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<script>
function periksaDeadlineTugas() {
    fetch('./cek_tugas_dl_terdekat.php')
        .then(response => response.json())
        .then(data => {
            if (data.ada_dekat) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 4000, 
                    timerProgressBar: true,
                    // Panggil kelas CSS animasi di sini
                    showClass: { popup: 'swal-slide-in' },
                    hideClass: { popup: 'swal-fade-out' },
                    didOpen: (toast) => {
                        toast.style.background = '#ffffff';
                        toast.style.borderRadius = '15px';
                        toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
                        toast.style.borderLeft = '6px solid #6c63ff';
                        // Tambahkan margin agar ada ruang dari tepi bawah
                        toast.style.marginBottom = '20px';
                        toast.style.marginRight = '20px';
                    }
                });

                Toast.fire({
                    icon: 'warning',
                    title: `<div style="color: #333;">⚠️ Deadline Dekat</div>`,
                    html: `
                        <div style="text-align: left;">
                            <b style="color: #6c63ff;">${data.matkul}</b><br>
                            <small>${data.judul}</small><br>
                            <span style="color: #ff4d4d; font-weight: bold;">Sisa: ${data.sisa_hari} hari lagi!</span>
                        </div>
                    `
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

document.addEventListener("DOMContentLoaded", periksaDeadlineTugas);
setInterval(periksaDeadlineTugas, 15000); 
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Fungsi untuk memicu notifikasi sukses
function tampilkanNotifSukses(pesan) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: pesan,
        confirmButtonColor: '#4e73df',
        confirmButtonText: 'OK'
    });
}

// Cek apakah ada session dari PHP
<?php if (isset($_SESSION['notif_sukses'])): ?>
    document.addEventListener("DOMContentLoaded", function() {
        tampilkanNotifSukses("<?= $_SESSION['notif_sukses']; ?>");
    });
    <?php unset($_SESSION['notif_sukses']); ?>
<?php endif; ?>
</script>
</body>
</html>