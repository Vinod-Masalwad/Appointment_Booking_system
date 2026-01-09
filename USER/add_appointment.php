<?php
session_start();
require "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id          = intval($_SESSION['user_id']);
    $service_id       = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // 1. CHECK IF SLOT IS TAKEN
    // We check if this specific service already has a booking at this date/time
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM appointments WHERE service_id = ? AND appointment_date = ? AND appointment_time = ?");
    mysqli_stmt_bind_param($check_stmt, "iss", $service_id, $appointment_date, $appointment_time);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        
        header("Location: ./user.php?status=exists");
        exit;
    }

    // 2. INSERT APPOINTMENT
    $stmt = mysqli_prepare($conn, "INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $service_id, $appointment_date, $appointment_time);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ./user.php?status=success");
    } else {
        header("Location: ./user.php?status=error");
    }
    exit;
}
?>  