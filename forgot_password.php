<?php
require "config.php";
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    exit("Email required");
}

$token = bin2hex(random_bytes(32));
$expires_sql = "DATE_ADD(NOW(), INTERVAL 15 MINUTE)";

$role = null;
$id   = null;
$name = null;

/* ===== CHECK ADMINS ===== */
$stmt = $conn->prepare("SELECT id, name FROM admins WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $role = "admin";
    $id   = $row['id'];
    $name = $row['name'];
}
$stmt->close();

/* ===== CHECK USERS (IF NOT ADMIN) ===== */
if ($role === null) {
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $role = "user";
        $id   = $row['id'];
        $name = $row['name'];
    }
    $stmt->close();
}

if ($role === null) {
    exit("❌ Email not registered");
}

/* ===== STORE TOKEN IN CORRECT TABLE ===== */
if ($role === "admin") {
    $stmt = $conn->prepare("
        UPDATE admins 
        SET reset_token=?, reset_expires=$expires_sql
        WHERE id=?
    ");
} else {
    $stmt = $conn->prepare("
        UPDATE users 
        SET reset_token=?, reset_expires=$expires_sql
        WHERE id=?
    ");
}

$stmt->bind_param("si", $token, $id);
$stmt->execute();
$stmt->close();

/* ===== RESET LINK ===== */
$resetLink = "http://localhost/APPOINTMENT/reset_password.php?token=$token";

/* ===== SEND MAIL ===== */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;

    $mail->setFrom(MAIL_USER, "Appointment System");
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Password Reset Request | Appointment System";
    $mail->Body = '
<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6f8;padding:30px;">
  <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:14px;
              overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.1);">

    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,#00eaff,#0077ff);
                padding:25px;text-align:center;">
      <h1 style="margin:0;color:#ffffff;font-size:24px;">
        Reset Your Password 🔐
      </h1>
      <p style="margin-top:8px;color:#e9faff;font-size:14px;">
        Secure access to your admin account
      </p>
    </div>

    <!-- CONTENT -->
    <div style="padding:30px;color:#333;">
      <h2 style="margin-top:0;">
        <b>Hello</b> 👋
      </h2>

      <p style="font-size:15px;line-height:1.6;">
        We received a request to reset your <b>admin account password</b>.
        Click the button below to proceed:
      </p>

      <!-- BUTTON -->
      <div style="text-align:center;margin:30px 0;">
        <a href="' . $resetLink . '" 
           style="display:inline-block;
                  padding:14px 26px;
                  background:linear-gradient(135deg,#00eaff,#00ff88);
                  color:#000;
                  font-weight:600;
                  text-decoration:none;
                  border-radius:30px;">
           🔁 Reset Password
        </a>
      </div>

      <!-- INFO BOX -->
      <div style="background:#f8fbff;border:1px solid #e3f1ff;
                  border-radius:12px;padding:18px;">
        <p style="margin:8px 0;">⏰ <b>Link Expiry:</b> 15 minutes</p>
        <p style="margin:8px 0;">🔒 <b>Security:</b> One-time use only</p>
      </div>

      <p style="font-size:14px;line-height:1.6;margin-top:20px;">
        If you did not request this password reset, you can safely ignore this email.
        Your account will remain secure.
      </p>

      <p style="margin-top:25px;">
        Regards,<br>
        <b>Appointment System Team</b> 💙
      </p>
    </div>

    <!-- FOOTER -->
    <div style="background:#f1f5f9;padding:15px;text-align:center;
                font-size:12px;color:#666;">
      © <b>' . date("Y") . ' Appointment Booking System</b>. All rights reserved.
    </div>

  </div>
</div>
';

    $mail->send();
    echo "✅ Reset link sent to your email";

} catch (Exception $e) {
    echo "❌ Mail error. Try again later.";
}
