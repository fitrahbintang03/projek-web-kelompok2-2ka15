<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Register</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="login.css?v=3">
  
  <style>
    /* CSS ANDA YANG SEBELUMNYA SAYA KEMBALIKAN */
    .alert-danger {
      background-color: #ffe0e3;
      color: #ff3344;
      border: 1px solid #ffccd1;
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
      font-weight: 500;
      animation: fadeIn 0.3s ease-in-out;
      opacity: 1;
      transition: opacity 0.5s ease, transform 0.5s ease, margin 0.5s ease, padding 0.5s ease;
    }

    .alert-danger.fade-out {
      opacity: 0;
      transform: translateY(-10px);
      margin-bottom: 0;
      padding: 0 15px;
      height: 0;
      overflow: hidden;
      border: none;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <script>
    <?php if (isset($_SESSION['logout_success'])) { ?>
        Swal.fire({
            icon: 'success',
            title: 'Sampai Jumpa!',
            text: '<?php echo $_SESSION['logout_success']; ?>',
            confirmButtonColor: '#4e73df',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['logout_success']); ?>
    <?php } ?>
  </script>

  <div class="bg-text-animation">
    <div class="marquee-track">
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
    </div>
    <div class="marquee-track reverse">
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
    </div>
    <div class="marquee-track">
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
      <span>2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp; 2KA15/KELOMPOK2 &nbsp;&bull;&nbsp;</span>
    </div>
  </div>
  
  <div class="login-body">
    <div class="login-container">
      <div class="login-box">
        <h1>Vclass.V2</h1>
        <p id="formTitle">Login ke akun Anda</p>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-danger" id="errorAlert">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <form id="formAuth" action="login.php" method="POST">
            <input type="text" id="username" name="username" placeholder="Masukkan Username" required>
            <input type="password" id="password" name="password" placeholder="Masukkan Password" required>

            <div id="registerInput" style="display:none;">
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi Password">
            </div>

            <button type="submit" id="mainButton" name="btn_submit">Login</button>
        </form>

        <p class="switch-text">
          <span id="switchText">Belum punya akun?</span>
          <a href="#" id="switchLink" onclick="toggleForm()">Register</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    // FUNGSI JAVASCRIPT ANDA TETAP SAMA
    let isLogin = true;
    function toggleForm(){
        isLogin = !isLogin;
        const form = document.getElementById("formAuth");
        const title = document.getElementById("formTitle");
        const button = document.getElementById("mainButton");
        const registerInput = document.getElementById("registerInput");
        const switchText = document.getElementById("switchText");
        const switchLink = document.getElementById("switchLink");
        const confirmPassword = document.getElementById("confirmPassword");

        if(isLogin){
            title.innerText = "Login ke akun Anda";
            button.innerText = "Login";
            registerInput.style.display = "none";
            switchText.innerText = "Belum punya akun?";
            switchLink.innerText = "Register";
            confirmPassword.value = ""; 
            confirmPassword.disabled = true; 
            form.action = "login.php"; 
        }else{
            title.innerText = "Buat akun baru";
            button.innerText = "Register";
            registerInput.style.display = "block";
            switchText.innerText = "Sudah punya akun?";
            switchLink.innerText = "Login";
            confirmPassword.disabled = false;
            confirmPassword.setAttribute("required", "true");
            form.action = "registerlogin.php"; 
        }
    }
    document.getElementById("confirmPassword").disabled = true;

    window.addEventListener("DOMContentLoaded", function () {
        const errorAlert = document.getElementById("errorAlert");
        if (errorAlert) {
            setTimeout(function () {
                errorAlert.classList.add("fade-out");
                setTimeout(function () {
                    errorAlert.remove();
                }, 500);
            }, 3500);
        }
    });
  </script> 
</body>
</html>