<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

if (!isset($_GET['id'], $_GET['service_id'])) {
    exit("Invalid request");
}

$id = (int)$_GET['id'];
$service_id = (int)$_GET['service_id'];

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM service_time_slots WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: admin.php?service_id=$service_id");
exit;
