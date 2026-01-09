<?php
session_start();
require "../config.php";

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

header("Content-Type: application/json");

if (!isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$id = intval($_POST['id']);

$sql = "DELETE FROM services WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "✅ success"]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}
exit;
