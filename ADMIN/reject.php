<?php
session_start();
require "../config.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: appointment.php");
    exit;
}

/* ===== FETCH DETAILS ===== */
$q = mysqli_query($conn, "
    SELECT a.*, u.name, u.email, s.service_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    WHERE a.id='$id'
");

$appt = mysqli_fetch_assoc($q);
if (!$appt) {
    header("Location: appointment.php");
    exit;
}

/* ===== UPDATE STATUS ===== */
mysqli_query($conn, "
    UPDATE appointments 
    SET status='cancelled' 
    WHERE id='$id'
");

/* ===== SEND MAIL ===== */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = MAIL_PORT;

    $mail->setFrom(MAIL_USER, 'Appointment System');
    $mail->addAddress($appt['email'], $appt['name']);

    $mail->isHTML(true);

   
    $mail->Subject = 'Appointment Update | Booking Cancelled';

    
    $mail->Body = '
<div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6f8;padding:30px;">
  <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:14px;
              overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.1);">

    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,#ff4d4d,#c0392b);
                padding:25px;text-align:center;">
      <h1 style="margin:0;color:#ffffff;font-size:24px;">
        Appointment Cancelled ❌
      </h1>
      <p style="margin-top:8px;color:#ffecec;font-size:14px;">
        Unfortunately, your booking could not be approved
      </p>
    </div>

    <!-- CONTENT -->
    <div style="padding:30px;color:#333;">
      <h2 style="margin-top:0;">
        <b>Hello ' . htmlspecialchars($appt['name']) . '</b> 👋
      </h2>

      <p style="font-size:15px;line-height:1.6;">
        We regret to inform you that your appointment has been 
        <b style="color:#e74c3c;">cancelled</b>.
        Below are the details of your booking:
      </p>

      <!-- DETAILS -->
      <div style="background:#fff5f5;border:1px solid #ffd6d6;
                  border-radius:12px;padding:20px;margin:25px 0;">
        <p style="margin:10px 0;"><b>🛎 Service:</b> ' . htmlspecialchars($appt['service_name']) . '</p>
        <p style="margin:10px 0;"><b>📅 Date:</b> ' . htmlspecialchars($appt['appointment_date']) . '</p>
        <p style="margin:10px 0;"><b>⏰ Time:</b> ' . htmlspecialchars($appt['appointment_time']) . '</p>
        <p style="margin:10px 0;">
          <b>📌 Status:</b> 
          <span style="color:#e74c3c;font-weight:bold;">Cancelled</span>
        </p>
      </div>

      <p style="font-size:14px;line-height:1.6;">
        You may try booking another slot at your convenience.
        We sincerely apologize for any inconvenience caused.
      </p>

      <p style="margin-top:25px;">
        Regards,<br>
        <b>Appointment System Team</b> ❤️
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

} catch (Exception $e) {
    
}

/* ===== REDIRECT ===== */
$_SESSION['error'] = "❌ Appointment cancelled and mail sent";
header("Location: admin.php");
exit;
