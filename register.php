<?php
session_start();
require "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;

/* ============================================================
   SEND OTP  (CHECK EMAIL BEFORE SENDING)
============================================================ */
if (isset($_POST['send_otp'])) {

    $email = $_POST['email'];

    // DB Connection
    $conn = new mysqli("localhost", "root", "", "appointment_system");

    if ($conn->connect_error) {
        echo "DB_CONN_ERROR";
        exit;
    }

    // Check in both tables
    $sql = "SELECT email FROM users WHERE email=? 
            UNION 
            SELECT email FROM admins WHERE email=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $stmt->store_result();

    // Email already exists
    if ($stmt->num_rows > 0) {
        echo "EMAIL_EXISTS";
        exit;
    }

    // Generate OTP for NEW users
    $otp = rand(1000, 9999);
    $_SESSION['otp'] = $otp;
    $_SESSION['email_temp'] = $email;

    // Send email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'appointment.booking.abs@gmail.com';
        $mail->Password   = 'nxfn qbxw upvm vayh';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('appointment.booking.abs@gmail.com', 'Appointment System');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your OTP Code | Appointment System";

$mail->Body = '
<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6f8;padding:25px;">
  <div style="max-width:500px;margin:auto;background:#ffffff;
              border-radius:14px;overflow:hidden;
              box-shadow:0 10px 25px rgba(0,0,0,0.1);">

    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,#00eaff,#0077ff);
                padding:20px;text-align:center;">
      <h2 style="margin:0;color:#ffffff;">
        OTP Verification 🔐
      </h2>
      <p style="margin-top:6px;color:#e9faff;font-size:13px;">
        Secure one-time verification code
      </p>
    </div>

    <!-- CONTENT -->
    <div style="padding:25px;color:#333;text-align:center;">
      <p style="font-size:15px;">
        Use the OTP below to continue:
      </p>

      <div style="font-size:26px;
                  letter-spacing:6px;
                  font-weight:bold;
                  color:#0077ff;
                  background:#f1f7ff;
                  padding:15px;
                  border-radius:10px;
                  margin:20px 0;">
        ' . htmlspecialchars($otp) . '
      </div>

      <p style="font-size:13px;color:#555;">
        ⏰ This OTP is valid for a short time only.<br>
        🔒 Do not share this code with anyone.
      </p>

      <p style="margin-top:20px;font-size:14px;">
        Regards,<br>
        <b>Appointment System Team</b> 💙
      </p>
    </div>

    <!-- FOOTER -->
    <div style="background:#f1f5f9;padding:12px;text-align:center;
                font-size:11px;color:#666;">
      © <b>' . date("Y") . ' Appointment Booking System</b>. All rights reserved.
    </div>

  </div>
</div>
';

$mail->send();
echo "OTP_SENT";


    } catch (Exception $e) {
        echo "MAIL_ERROR";
    }
    exit;
}



/* ============================================================
   VERIFY OTP
============================================================ */
if (isset($_POST['verify_otp'])) {

    $userOtp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $userOtp == $_SESSION['otp']) {
        echo "OTP_CORRECT";
    } else {
        echo "WRONG_OTP";
    }
    exit;
}


/* ============================================================
   FINAL REGISTRATION (INSERT INTO DB)
============================================================ */
if (isset($_POST['register'])) {

    // DB Connection
    $conn = new mysqli("localhost", "root", "", "appointment_system");

    if ($conn->connect_error) {
        echo "DB_CONN_ERROR";
        exit;
    }

    // Data from JS
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // Hash password
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

    // Choose correct table
    if ($role === "admin") {
        $table = "admins";
    } else {
        $table = "users";
    }

    // Insert query
    $sql = "INSERT INTO $table (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("sss", $name, $email, $hashedPass);

    if ($stmt->execute()) {
        echo "REGISTERED";
    } else {
        echo "DB_ERROR";
    }

    exit;
}

/* ============================================================
   CHECK IF EMAIL EXISTS
============================================================ */
if (isset($_POST['check_email'])) {

    $email = $_POST['email'];
    $conn = new mysqli("localhost", "root", "", "appointment_system");

    $sql1 = $conn->query("SELECT id FROM users WHERE email='$email'");
    $sql2 = $conn->query("SELECT id FROM admins WHERE email='$email'");

    if ($sql1->num_rows > 0 || $sql2->num_rows > 0) {
        echo "EXISTS";
    } else {
        echo "NOT_EXISTS";
    }
    exit;
}

?>




<html>
<head>
    <title>REGISTER</title>

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anta&family=Audiowide&family=Lexend:wght@100..900&family=Racing+Sans+One&family=Science+Gothic:wght@100..900&display=swap">

    <!-- MATERIAL ICONS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1">
</head>

<body>

    <!-- NAVBAR -->
    <nav>
        <div class="logo">APPOINTMENT BOOKING SYSTEM</div>
        <div class="hamburger" onclick="toggleMenu()">☰</div>
    </nav>

    <!-- MAIN CONTAINER -->
    <div class="home_contianer">

        <div class="form_wrapper">
            <h1 class="register">REGISTER</h1>

            <!-- ROLE SELECTION -->
            <div class="role-select">
                <div class="role-option user" data-role="user">
                    <span class="material-symbols-outlined role-icon">person</span>
                    <p>User</p>
                </div>

                <div class="role-option admin" data-role="admin">
                    <span class="material-symbols-outlined role-icon">shield_person</span>
                    <p>Admin</p>
                </div>
            </div>

            <!-- REGISTER FORM -->
            <form id="regForm">

                <div class="form__group field">
                    <input type="text" class="form__field" placeholder="Full Name" required>
                    <label class="form__label">Full Name</label>
                    <span class="material-symbols-outlined icon">person</span>
                </div>

                <div class="form__group field">
                    <input type="text" id="email" class="form__field" placeholder="Gmail" required>
                    <label class="form__label">Gmail</label>
                    <span class="material-symbols-outlined icon">mail</span>
                    <p id="emailError" style="color: red; display: none; font-size: 12px; margin-top: -10px;"></p>
                    <p id="verifiedText">✔ Email Verified</p>
                </div>

                

                <div class="form__group field">
                    <input type="password" id="password1" class="form__field" placeholder="Password" required>
                    <label class="form__label">Password</label>
                    <span class="material-symbols-outlined icon" id="togglePass1">visibility_off</span>
                </div>

                <div class="form__group field">
                    <input type="password" id="password2" class="form__field" placeholder="Confirm Password" required>
                    <label class="form__label">Confirm Password</label>
                    <span class="material-symbols-outlined icon" id="togglePass2">visibility_off</span>
                </div>

                <div class="btn-reg">
                    <a href="#" id="btn1">Register</a>
                </div>

            </form>
        </div>

        <div class="reg_svg">
            <img src="./images/undraw_teamwork_zplp.svg" alt="">
        </div>

    </div>

    <!-- OTP POPUP -->
    <div id="otpOverlay" class="otp-overlay-hidden">
        <div id="otpCard" class="otp-card">

                

            <form class="form">
                <div class="content">

                    <p align="center">VERIFY OTP</p>

                    <div class="inp">
                        <input type="text" class="input" maxlength="1" id="otp1">
                        <input type="text" class="input" maxlength="1" id="otp2">
                        <input type="text" class="input" maxlength="1" id="otp3">
                        <input type="text" class="input" maxlength="1" id="otp4">
                    </div>

                    <button type="button" id="btn-v">Verify</button>


                    <svg class="svg" viewBox="0 0 200 200">
                        <path class="path"
                            d="M56.8,-23.9C61.7,-3.2,45.7,18.8,26.5,31.7C7.2,44.6,-15.2,48.2,-35.5,36.5C-55.8,24.7,-73.9,-2.6,-67.6,-25.2C-61.3,-47.7,-30.6,-65.6,-2.4,-64.8C25.9,-64.1,51.8,-44.7,56.8,-23.9Z"
                            transform="translate(100 100)">
                        </path>
                    </svg>

                </div>
            </form>

        </div>
    </div>

    <!-- ERROR TOAST -->
    <div id="errorToast"></div>

    <!-- MAIN JS -->
    <script src="./JAVASCRIPTS/register.js"></script>

</body>
</html>
