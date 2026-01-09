<?php
session_start();
require "../config.php";

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

/* ===== ADD SLOT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service_id = intval($_POST['service_id']);
    $hour       = intval($_POST['hour']);
    $minute     = str_pad($_POST['minute'], 2, '0', STR_PAD_LEFT);
    $ampm       = $_POST['ampm'];

    // Convert to 24-hour format
    if ($ampm === 'PM' && $hour != 12) $hour += 12;
    if ($ampm === 'AM' && $hour == 12) $hour = 0;

    $slot_time = sprintf("%02d:%s:00", $hour, $minute);

    /* ===== DUPLICATE SLOT CHECK ===== */
    $check = mysqli_prepare(
        $conn,
        "SELECT id FROM service_time_slots 
         WHERE service_id = ? AND slot_time = ?"
    );
    mysqli_stmt_bind_param($check, "is", $service_id, $slot_time);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $_SESSION['flash_error'] = "❌ Slot already exists!";
        header("Location: admin.php?service_id=$service_id");
        exit;
    }

    /* ===== INSERT SLOT ===== */
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO service_time_slots (service_id, slot_time) VALUES (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "is", $service_id, $slot_time);
    mysqli_stmt_execute($stmt);

    $_SESSION['flash_success'] = "✅ Slot added successfully!";
    header("Location: admin.php?service_id=$service_id");
    exit;
}
