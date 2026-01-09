<?php
session_start();
require "../config.php";

/* ===== ADMIN LOGIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

/* ===== VALIDATE ID ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: appointment.php?error=invalid");
    exit;
}

$appointment_id = intval($_GET['id']);

/* ===== UPDATE STATUS TO COMPLETED ===== */
$stmt = mysqli_prepare(
    $conn,
    "UPDATE appointments 
     SET status = 'completed' 
     WHERE id = ? AND status = 'confirmed'"
);

mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);

/* ===== CHECK UPDATE ===== */
if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: admin.php?success=completed");
} else {
    header("Location: admin.php?error=not_allowed");
}

exit;
?>
