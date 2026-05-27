<?php
// 1. Hubungkan langsung ke database
$host     = "localhost";
$username = "root";
$password = "kelompok2web."; 
$database = "pbokelompok2";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 2. Tangkap data yang dikirim dari form login_html.php
$user = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

// 3. UBAH PASSWORD JADI HASH (Enkripsi)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// ====================================================================
// 4. SEBELUM INSERT, CEK DULU APAKAH USERNAME SUDAH DI-REGISTER ORANG LAIN
// ====================================================================
$cek_username = mysqli_query($conn, "SELECT username FROM users WHERE username = '$user'");

if (mysqli_num_rows($cek_username) > 0) {
    // ----------------------------------------------------------------
    // KONDISI 1: GAGAL REGISTER KARENA USERNAME KEMBAR (Kustom Card)
    // ----------------------------------------------------------------
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Register Gagal</title>
        <style>
            body {
                background: linear-gradient(135deg, #5c72ff, #8c3fff);
                display: flex; justify-content: center; align-items: center;
                height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif;
            }
            .error-card {
                background: white; padding: 35px 30px; border-radius: 15px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center;
                max-width: 400px; width: 90%; animation: popUp 0.4s ease-in-out;
            }
            .error-icon { font-size: 55px; color: #e74c3c; margin-bottom: 15px; }
            .error-card h2 { color: #333; margin: 0 0 10px 0; }
            .error-card p { color: #666; font-size: 14px; line-height: 1.5; margin-bottom: 25px; }
            .btn-back {
                background: #5c72ff; color: white; border: none; padding: 10px 25px;
                border-radius: 8px; cursor: pointer; text-decoration: none;
                display: inline-block; font-size: 14px; font-weight: 500;
            }
            @keyframes popUp {
                from { transform: scale(0.8); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">❌</div>
            <h2>Register Gagal!</h2>
            <p>Username <b>"<?php echo htmlspecialchars($user); ?>"</b> sudah terdaftar. Silakan gunakan username unik yang lain.</p>
            <a href="login_html.php" class="btn-back">Kembali Ke Form</a>
        </div>
    </body>
    </html>
    <?php
    exit();

} else {
    // ----------------------------------------------------------------
    // KONDISI 2: USERNAME AMAN, LANJUTKAN SIMPAN KE DATABASE (Sukses Card)
    // PERBAIKAN TOTAL: Menambahkan email, npm, dan kelas sekaligus dengan string kosong
    // ----------------------------------------------------------------
    $sql = "INSERT INTO users (username, password, email, npm, kelas) VALUES ('$user', '$passwordHash', '', '', '')";

    if (mysqli_query($conn, $sql)) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Register Berhasil</title>
            <style>
                body {
                    background: linear-gradient(135deg, #5c72ff, #8c3fff);
                    display: flex; justify-content: center; align-items: center;
                    height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif;
                }
                .success-card {
                    background: white; padding: 35px 30px; border-radius: 15px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center;
                    max-width: 400px; width: 90%; animation: popUp 0.4s ease-in-out;
                }
                .success-icon { font-size: 55px; color: #2ecc71; margin-bottom: 15px; }
                .success-card h2 { color: #333; margin: 0 0 10px 0; }
                .success-card p { color: #666; font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
                .redirect-text { font-size: 13px; color: #999; }
                #countdown { font-weight: bold; color: #5c72ff; }
                @keyframes popUp {
                    from { transform: scale(0.8); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
            </style>
        </head>
        <body>
            <div class="success-card">
                <div class="success-icon">☑️</div>
                <h2>Register Berhasil!</h2>
                <p>Akun Anda telah terdaftar di sistem. Silakan masuk menggunakan username dan password baru Anda.</p>
                <div class="redirect-text">Mengalihkan ke halaman login dalam <span id="countdown">4</span> detik...</div>
            </div>

            <script>
                let seconds = 4;
                const countdownElement = document.getElementById("countdown");
                const interval = setInterval(() => {
                    seconds--;
                    countdownElement.innerText = seconds;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = 'login_html.php';
                    }
                }, 1000);
            </script>
        </body>
        </html>
        <?php
        exit();
    } else {
        echo "Error murni database: " . mysqli_error($conn);
    }
}
?>