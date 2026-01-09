<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    echo "unauthorized";
    exit;
}

$admin_id = $_SESSION['admin_id'];

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    echo "mismatch";
    exit;
}

if (strlen($new) < 6) {
    echo "weak";
    exit;
}

/* FETCH CURRENT PASSWORD */
$q = mysqli_query($conn, "SELECT password FROM admins WHERE id=$admin_id");
$row = mysqli_fetch_assoc($q);

if (!$row || !password_verify($current, $row['password'])) {
    echo "wrong_current";
    exit;
}

/* PREVENT SAME PASSWORD */
if (password_verify($new, $row['password'])) {
    echo "same_password";
    exit;
}

/* UPDATE */
$hashed = password_hash($new, PASSWORD_DEFAULT);
mysqli_query($conn, "UPDATE admins SET password='$hashed' WHERE id=$admin_id");

echo "success";
