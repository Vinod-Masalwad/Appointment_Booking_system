<?php
session_start();
require "../config.php";

if (!isset($_SESSION['user_id'])) {
    exit("❌ Unauthorized access");
}

$user_id = $_SESSION['user_id'];

$current = $_POST['current'] ?? '';
$new     = $_POST['new'] ?? '';
$confirm = $_POST['confirm'] ?? '';

if ($current === '' || $new === '' || $confirm === '') {
    exit("⚠️ All fields are required");
}

if ($new !== $confirm) {
    exit("❌ Passwords do not match");
}

if (strlen($new) < 6) {
    exit("⚠️ Password must be at least 6 characters");
}

/* FETCH CURRENT PASSWORD */
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    exit("❌ User not found");
}

$user = $result->fetch_assoc();

/* VERIFY CURRENT PASSWORD */
if (!password_verify($current, $user['password'])) {
    exit("❌ Current password incorrect");
}

/* UPDATE PASSWORD */
$hashed = password_hash($new, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param("si", $hashed, $user_id);
$update->execute();

echo "✅ Password updated successfully";
?>
