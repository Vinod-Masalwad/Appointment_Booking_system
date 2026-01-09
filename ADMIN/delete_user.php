<?php
session_start();
require "../config.php";

/* ===============================
   ADMIN CHECK
================================ */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   VALIDATE ID
================================ */
if (!isset($_GET['id'])) {
    echo "<script>alert('❌ Invalid user ID'); window.history.back();</script>";
    exit;
}

$user_id = intval($_GET['id']);

/* ===============================
   DELETE USER (CASCADE SAFE)
================================ */

// Delete profile first
mysqli_query($conn, "DELETE FROM user_profiles WHERE user_id = $user_id");

// Delete appointments 
mysqli_query($conn, "DELETE FROM appointments WHERE user_id = $user_id");

//Delete service time slots
mysqli_query($conn, "DELETE FROM service_time_slots WHERE user_id = $user_id");

// Delete main user
$deleteUser = mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");

/* ===============================
   RESULT
================================ */
if ($deleteUser) {
    echo "<script>
        alert('✅ User deleted successfully ');
        window.location.href = 'admin.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Error deleting user ');
        window.history.back();
    </script>";
}
