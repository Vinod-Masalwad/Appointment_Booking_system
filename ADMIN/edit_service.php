<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

/* ===== AJAX UPDATE LOGIC ===== */
if (isset($_POST['ajax_edit_service'])) {
    header("Content-Type: application/json");
    $id = intval($_POST['id']);
    $service_name = trim($_POST['service_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');

    if ($service_name === '') {
        echo json_encode(["status" => "error", "message" => "Service name is required"]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE services SET service_name=?, description=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $service_name, $description, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Service updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

/* ===== FETCH DATA FOR DISPLAY ===== */
$id = intval($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM services WHERE id=$id");
if (!$result || mysqli_num_rows($result) === 0) { exit("Service not found"); }
$service = mysqli_fetch_assoc($result);
?>

<h2 class="modal-title">Edit Service</h2>

<form id="editServiceForm" method="post" action="edit_service.php">
    <input type="hidden" name="ajax_edit_service" value="1">
    <input type="hidden" name="id" value="<?= $service['id'] ?>">

    <div class="form-group">
        <label>Service Name</label>
        <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($service['description']) ?></textarea>
    </div>

    <button type="submit" class="save-btn">Update Service</button>
</form>
<script>

document.addEventListener("submit", function (e) {

    if (e.target.id === "editServiceForm") {
        e.preventDefault(); 

        fetch("edit_service.php", {
            method: "POST",
            body: new FormData(e.target)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("Service Updated Successfully");
                document.getElementById("serviceModal").style.display = "none";
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert("Something went wrong"));
    }

});
</script>