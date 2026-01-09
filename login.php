<?php
session_start();
$conn = new mysqli("localhost", "root", "", "appointment_system");

if ($conn->connect_error) {
    exit("DB_ERROR");
}

/* ================= ROLE CHECK (FOR FORGOT PASSWORD) ================= */
/* ================= FORGOT PASSWORD ROLE CHECK ================= */
if (isset($_POST['check_role'])) {

    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        echo "EMAIL_REQUIRED";
        exit;
    }

    /* ---- CHECK ADMIN ---- */
    $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "admin";
        exit;
    }
    $stmt->close();

    /* ---- CHECK USER ---- */
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "user";
        exit;
    }

    echo "not_found";
    exit;
}

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === '' || $password === '') {
        echo "EMPTY";
        exit;
    }

    // USER LOGIN
    $stmtU = $conn->prepare("SELECT id, password FROM users WHERE email=? LIMIT 1");
    $stmtU->bind_param("s", $email);
    $stmtU->execute();
    $stmtU->store_result();

    if ($stmtU->num_rows === 1) {
        $stmtU->bind_result($id, $hash);
        $stmtU->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = "user";
            echo "USER_OK";
            exit;
        } else {
            echo "WRONG_PASS";
            exit;
        }
    }

    // ADMIN LOGIN
    $stmtA = $conn->prepare("SELECT id, password FROM admins WHERE email=? LIMIT 1");
    $stmtA->bind_param("s", $email);
    $stmtA->execute();
    $stmtA->store_result();

    if ($stmtA->num_rows === 1) {
        $stmtA->bind_result($id, $hash);
        $stmtA->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['role'] = "admin";
            echo "ADMIN_OK";
            exit;
        } else {
            echo "WRONG_PASS";
            exit;
        }
    }

    echo "NO_ACCOUNT";
    exit;
}
?>



<html>
<head>

    <title>LOGIN</title>

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anta&family=Audiowide&family=Lexend:wght@100..900&family=Racing+Sans+One&family=Science+Gothic:wght@100..900&display=swap">

    <!-- MATERIAL ICONS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" />

</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">APPOINTMENT BOOKING SYSTEM</div>
    <div class="hamburger" onclick="toggleMenu()">☰</div>
</nav>

<div class="home_contianer">

    <div class="form_wrapper">
        <h1 class="register">LOGIN</h1>

        <div class="form__group field">
            <input type="text" id="email" class="form__field" placeholder="Gmail">
            <label class="form__label">Gmail</label>
            <span class="material-symbols-outlined icon">mail</span>
            <p id="emailError" style="color:red;display:none;font-size:12px;margin-top:-10px;"></p>
        </div>
        
        <div class="form__group field">
            <input type="password" id="password" class="form__field" placeholder="Password">
            <label class="form__label">Password</label>
            <span class="material-symbols-outlined icon" id="togglePass1">visibility_off</span>
        </div>

        <div class="btn-reg">
            <button id="btn1">Login</button>
        </div>

        <div class="forgot-wrap">
            <a href="#" onclick="openForgotModal()">Forgot Password?</a>
        </div>

    </div>

    <div class="log_svg">
        <img src="./images/Signup concept illustration_04.svg" alt="">
    </div>

</div>

<div class="modal1" id="forgotPasswordModal">
    <div class="modal-box">
        <h3>Forgot Password</h3>
        <p class="muted-text">
            Reset link will be sent to your registered email.
        </p>

        <form id="forgotPasswordForm">
            <input type="email" name="email" placeholder="Registered Email" required>

            <button type="submit" class="settings-btn primary full">
                Send Reset Link
            </button>
        </form>

        
        <button class="modal-close" onclick="closeForgotModal()">×</button>
    </div>
</div>

<!-- TOAST -->
<div id="errorToast"></div>

<!-- LOGIN.JS FILE -->
<script src="./JAVASCRIPTS/login.js"></script>

</body>
</html>

<script>
function openForgotModal() {
    document.getElementById("forgotPasswordModal").classList.add("show");
}

function closeForgotModal() {
    document.getElementById("forgotPasswordModal").classList.remove("show");
}

/* FORGOT PAGE JS ===============================================================================================*/

document.getElementById("forgotPasswordForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("forgot_password.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        this.reset();
    });
});

</script>