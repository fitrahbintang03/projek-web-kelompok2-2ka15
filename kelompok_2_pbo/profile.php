<?php 
// 1. WAJIB: Jalankan session di baris paling pertama untuk membaca kiriman login
session_start(); 

// 2. PROTEKSI: Jika user mencoba masuk langsung tanpa login, tendang kembali ke form login
if (!isset($_SESSION['username'])) {
    header("Location: login_html.php");
    exit();
}

// 3. KONEKSI DATABASE
include 'koneksi.php'; 

// Ambil username dari session untuk query data profil lengkap
$username_session = $_SESSION['username'];

// Variabel penanda status untuk memicu toast notification dari samping
$status_update = ""; 

// =========================================================================
// PROSES SIMPAN / UPDATE PROFILE (DARI TOMBOL EDIT PROFIL VIA POST)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $email_baru = mysqli_real_escape_string($conn, $_POST['email']);
    $npm_baru = mysqli_real_escape_string($conn, $_POST['npm']);
    $kelas_baru = mysqli_real_escape_string($conn, $_POST['kelas']); 
    
    // --- VALIDASI EMAIL GANDA ---
    $q_cek_email = "SELECT * FROM users WHERE email = '$email_baru' AND username != '$username_session'";
    $sql_cek_email = mysqli_query($conn, $q_cek_email);
    
    if (mysqli_num_rows($sql_cek_email) > 0) {
        $status_update = "email_kembar";
    } else {
        $q_update = "UPDATE users SET email = '$email_baru', npm = '$npm_baru', kelas = '$kelas_baru' WHERE username = '$username_session'";
        if (mysqli_query($conn, $q_update)) {
            $status_update = "sukses";

            // --- TAMBAHAN LOGIKA: Catat Aktivitas Mengubah Profil ke Tabel Aktivitas ---
            $aktivitas_profil = "Mengubah data profil (Email/NPM/Kelas)";
            $q_log_profil = "INSERT INTO aktivitas (username, aktivitas, waktu) VALUES ('$username_session', '$aktivitas_profil', NOW())";
            mysqli_query($conn, $q_log_profil);
            
        } else {
            $status_update = "gagal";
        }
    }
}

// =========================================================================
// QUERY AMBIL DATA PROFIL USER YANG SEDANG LOGIN
// =========================================================================
$q_user = "SELECT * FROM users WHERE username = '$username_session'";
$sql_user = mysqli_query($conn, $q_user);
$data_user = mysqli_fetch_assoc($sql_user);

$email_user = isset($data_user['email']) ? $data_user['email'] : 'Belum diatur';
$npm_user = isset($data_user['npm']) ? $data_user['npm'] : 'Belum diatur';
$kelas_user = isset($data_user['kelas']) ? $data_user['kelas'] : '1KA10'; 

// =========================================================================
// QUERY SINKRONISASI ANGKA INDIKATOR PROFIL SECARA DINAMIS
// =========================================================================
// Ambil id_user dari session
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;

// 1. Tugas Belum Dikumpulkan (Khusus untuk user yang login)
$q_count_mendatang = "SELECT COUNT(*) as total FROM tugas 
                      WHERE deadline >= NOW() 
                      AND id_tugas NOT IN (SELECT id_tugas FROM pengumpulan WHERE id_user = '$id_user')";
$sql_count_mendatang = mysqli_query($conn, $q_count_mendatang);
$res_count_mendatang = mysqli_fetch_assoc($sql_count_mendatang);
$total_mendatang = $res_count_mendatang['total'];

// 2. Tugas Selesai (Khusus untuk user yang login)
$q_count_selesai = "SELECT COUNT(*) as total FROM pengumpulan WHERE id_user = '$id_user'";
$sql_count_selesai = mysqli_query($conn, $q_count_selesai);
$res_count_selesai = mysqli_fetch_assoc($sql_count_selesai);
$total_selesai = $res_count_selesai['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>VclassV2ByKelompok2 - Profil</title>
    <link rel="stylesheet" href="desain.css?v=2">
    
    <style>
        /* Toast Notification Base Style */
        .toast-container {
            position: fixed !important;
            top: 30px !important;
            right: -400px !important; /* Tersembunyi di luar layar kanan */
            color: white !important;
            padding: 16px 28px !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            font-family: 'Segoe UI', system-ui, sans-serif !important;
            font-weight: 600 !important;
            z-index: 999999 !important;
            opacity: 0 !important; /* Default tidak terlihat */
            /* Transinisi gabungan untuk pergerakan masuk dan efek memudar */
            transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.5s ease !important;
        }

        /* Variasi Toast Sukses (Hijau) */
        .toast-success {
            background-color: #2ecc71 !important;
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.3) !important;
        }

        /* Variasi Toast Gagal / Email Kembar (Merah) */
        .toast-danger {
            background-color: #e74c3c !important;
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.3) !important;
        }

        /* Class saat Toast Aktif Muncul */
        .toast-container.show {
            right: 30px !important; 
            opacity: 1 !important;
        }

        /* Class Khusus saat Proses Menghilang (Fading Out) */
        .toast-container.fade-out {
            opacity: 0 !important;
            right: 15px !important; /* Sedikit bergeser ke kanan saat memudar halus */
        }

        /* Modal Pop-up Box Overlay (Latar Belakang Gelap) */
        .custom-modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(5px) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 99999 !important;
            opacity: 0 !important;
            pointer-events: none !important;
            transition: opacity 0.3s ease !important;
        }

        .custom-modal-overlay.open {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        /* Kotak Putih Modal */
        .custom-modal {
            background: #ffffff !important;
            padding: 30px !important;
            border-radius: 20px !important;
            width: 90% !important;
            max-width: 420px !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3) !important;
            transform: translateY(-50px) !important;
            transition: transform 0.3s ease !important;
            box-sizing: border-box !important;
            font-family: 'Segoe UI', system-ui, sans-serif !important;
        }

        .custom-modal-overlay.open .custom-modal {
            transform: translateY(0) !important;
        }

        .custom-modal h3 {
            margin-top: 0 !important;
            margin-bottom: 24px !important;
            color: #2d3436 !important;
            font-size: 22px !important;
            font-weight: 700 !important;
            text-align: center !important;
        }

        .modal-form-group {
            margin-bottom: 18px !important;
            text-align: left !important;
        }

        .modal-form-group label {
            display: block !important;
            font-size: 14px !important;
            color: #636e72 !important;
            margin-bottom: 8px !important;
            font-weight: 600 !important;
        }

        .modal-form-group input {
            width: 100% !important;
            padding: 12px 16px !important;
            border: 1.5px solid #dfe6e9 !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
            font-size: 15px !important;
            background-color: #fbfbfb !important;
            color: #2d3436 !important;
            transition: all 0.2s ease !important;
        }

        .modal-form-group input:focus {
            outline: none !important;
            border-color: #6c5ce7 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1) !important;
        }

        .modal-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 12px !important;
            margin-top: 28px !important;
        }

        .btn-modal-cancel {
            background: #f1f2f6 !important;
            color: #57606f !important;
            border: none !important;
            padding: 12px 20px !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: background 0.2s !important;
        }
        .btn-modal-cancel:hover { background: #e4e7eb !important; }

        .btn-modal-save {
            background: #6c5ce7 !important;
            color: white !important;
            border: none !important;
            padding: 12px 20px !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: background 0.2s !important;
        }
        .btn-modal-save:hover { background: #5b4cc4 !important; }
    </style>
</head>
<body>

<div id="toastNotif" class="toast-container">
    <span id="toastText"></span>
</div>

<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal">
        <h3>Edit Profil Anda</h3>
        <form action="profile.php" method="POST" onsubmit="return validasiForm()">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="modal-form-group">
                <label>Alamat Email</label>
                <input type="email" name="email" id="modal_email" value="<?= htmlspecialchars($email_user); ?>" required>
            </div>
            
            <div class="modal-form-group">
                <label>NPM</label>
                <input type="text" name="npm" id="modal_npm" value="<?= htmlspecialchars($npm_user); ?>" required>
            </div>
            
            <div class="modal-form-group">
                <label>Kelas</label>
                <input type="text" name="kelas" id="modal_kelas" value="<?= htmlspecialchars($kelas_user); ?>" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="tutupModal()">Batal</button>
                <button type="submit" class="btn-modal-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <ul class="menu">
            <li onclick="window.location.href='dashboard.php?page=beranda'">🏠 Beranda</li>
            <li onclick="window.location.href='dashboard.php?page=tugas'">📄 Tugas Saya</li>
            <li class="active">👤 Profil</li>
        </ul>
    </div>
    
    <div class="main">
        <div id="profil" class="page active-page">
            <div class="header">
                <h2>Profil Saya</h2>
                <p>Hai, <?= htmlspecialchars($_SESSION['username']); ?> 👋</p>
            </div>

            <div class="profile-main-card">
                <img src="https://i.pravatar.cc/120" alt="foto profil" class="profile-avatar">
                <div class="profile-info-block">
                    <h2 class="profile-name"><?= htmlspecialchars($_SESSION['username']); ?></h2>
                    <div class="badge-mahasiswa">
                        Mahasiswa
                    </div>
                    <p class="profile-email" id="display_email_top"><?= htmlspecialchars($email_user); ?></p>
                    <p class="profile-meta" id="display_npm_top">NPM: <?= htmlspecialchars($npm_user); ?></p>
                    <p class="profile-meta-bottom">Kelas: <span id="display_kelas_top"><?= htmlspecialchars($kelas_user); ?></span></p>
                    <button class="btn-edit-profile" onclick="bukaModal()">Edit Profil</button>
                </div>
            </div>

            <div class="top-cards stat-box-container">
                <div class="mini-card">
                    <h1 class="stat-number-blue"><?= $total_mendatang; ?></h1>
                    <small>Tugas Belum Dikumpulkan</small>
                </div>
                <div class="mini-card">
                    <h1 class="stat-number-green"><?= $total_selesai; ?></h1>
                    <small>Tugas Selesai</small>
                </div>
            </div>

            <div class="content">
                <div class="card left">
                    <h3>Informasi Pribadi</h3>
                    <div class="info-row">
                        <small class="info-row-title">Nama Lengkap</small>
                        <div class="info-row-value"><?= htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <small class="info-row-title">Email</small>
                        <div class="info-row-value" id="display_email_bottom"><?= htmlspecialchars($email_user); ?></div>
                    </div>
                    <div class="info-row">
                        <small class="info-row-title">NPM</small>
                        <div class="info-row-value" id="display_npm_bottom"><?= htmlspecialchars($npm_user); ?></div>
                    </div>
                    <div class="info-row">
                        <small class="info-row-title">Kelas</small>
                        <div class="info-row-value" id="display_kelas_bottom"><?= htmlspecialchars($kelas_user); ?></div>
                    </div>
                    <br>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>

                <div class="card right">
                    <h3>Aktivitas Terbaru</h3>
                    <?php
                    $q_activity = "SELECT aktivitas, waktu FROM aktivitas WHERE username = '$username_session' ORDER BY waktu DESC LIMIT 3";
                    $sql_activity = mysqli_query($conn, $q_activity);

                    if (mysqli_num_rows($sql_activity) > 0) {
                        while ($activity = mysqli_fetch_assoc($sql_activity)) {
                            $waktu_formatted = date('d M Y - H:i', strtotime($activity['waktu']));
                            ?>
                            <div class="activity-item activity-spacing">
                                <div>
                                    <b><?= htmlspecialchars($activity['aktivitas']); ?></b><br>
                                    <small class="activity-time"><?= $waktu_formatted; ?></small>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p style='color:#888; font-size:14px;'>Belum ada aktivitas terbaru.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bukaModal() {
    document.getElementById('modalEdit').classList.add('open');
}

function tutupModal() {
    document.getElementById('modalEdit').classList.remove('open');
}

function validasiForm() {
    var email = document.getElementById('modal_email').value;
    var npm = document.getElementById('modal_npm').value;
    var kelas = document.getElementById('modal_kelas').value;

    if (email.trim() === "" || npm.trim() === "" || kelas.trim() === "") {
        alert("Email, NPM, dan Kelas tidak boleh kosong!");
        return false;
    }
    return true;
}

// LOGIKA ANIMASI TOAST FADING OUT
window.addEventListener('DOMContentLoaded', (event) => {
    var toast = document.getElementById('toastNotif');
    var toastText = document.getElementById('toastText');
    var status = "<?= $status_update; ?>";

    if (status === "sukses") {
        toastText.innerText = "✨ Profil Anda berhasil diperbarui!";
        toast.classList.add('toast-success', 'show');
        jalankanEfekMudar(toast, 4000);
    } 
    else if (status === "email_kembar") {
        toastText.innerText = "⚠️ Gagal! Email tersebut sudah terdaftar oleh user lain.";
        toast.classList.add('toast-danger', 'show');
        jalankanEfekMudar(toast, 4500); 
    } 
    else if (status === "gagal") {
        toastText.innerText = "❌ Terjadi kesalahan sistem saat memperbarui profil.";
        toast.classList.add('toast-danger', 'show');
        jalankanEfekMudar(toast, 4000);
    }
});

// Fungsi pembantu untuk memproses efek memudar halus (Fading)
function jalankanEfekMudar(elemen, durasi) {
    setTimeout(function() {
        elemen.classList.add('fade-out');
        setTimeout(function() {
            elemen.classList.remove('show', 'fade-out');
        }, 500);
    }, durasi);
}
</script>
</body>
</html>