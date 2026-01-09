<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

if (isset($_POST['ajax_add_service'])) {
    header("Content-Type: application/json");

    $service_name = trim($_POST['service_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    
    // Get the time slot arrays
    $hours   = $_POST['hours'] ?? [];
    $minutes = $_POST['minutes'] ?? [];
    $ampms   = $_POST['ampms'] ?? [];

    if ($service_name === '') {
        echo json_encode(["status" => "error", "message" => "Service name required"]);
        exit;
    }

    /* INSERT SERVICE */
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO services (service_name, description, is_active) VALUES (?, ?, 1)"
    );
    mysqli_stmt_bind_param($stmt, "ss", $service_name, $description);
    mysqli_stmt_execute($stmt);

    $service_id = mysqli_insert_id($conn);

    /* INSERT SLOTS */
    $slotStmt = mysqli_prepare(
        $conn,
        "INSERT INTO service_time_slots (service_id, slot_time) VALUES (?, ?)"
    );

    // Loop through the arrays
    for ($i = 0; $i < count($hours); $i++) {
        if (!empty($hours[$i]) && !empty($minutes[$i]) && !empty($ampms[$i])) {
            $hour = intval($hours[$i]);
            $minute = $minutes[$i];
            $ampm = $ampms[$i];
            
            // Convert to 24-hour format
            if ($ampm === 'PM' && $hour !== 12) {
                $hour += 12;
            }
            if ($ampm === 'AM' && $hour === 12) {
                $hour = 0;
            }
            
            // Format time string
            $timeString = sprintf("%02d:%s:00", $hour, $minute);
            
            mysqli_stmt_bind_param($slotStmt, "is", $service_id, $timeString);
            mysqli_stmt_execute($slotStmt);
        }
    }

    echo json_encode(["status" => "success"]);
    exit;
}
?>


<h2 class="modal-title">Add New Service</h2>

<style>
/* =========================
   ADD SERVICE – TIME SELECTS
========================= */

#addServiceForm .slot-inputs {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
}

#addServiceForm .slot-inputs select {
    height: 44px;
    padding: 0 12px;
    border-radius: 12px;
    border: 1px solid #2AD7FF;
    background-color: #0b0b0b;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    outline: none;
    cursor: pointer;
}

/* Colon */
#addServiceForm .slot-inputs span {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
}

/* Focus effect */
#addServiceForm .slot-inputs select:focus {
    box-shadow: 0 0 0 2px rgba(42, 215, 255, 0.35);
}
</style>

<form id="addServiceForm" method="POST">
    <input type="hidden" name="ajax_add_service" value="1">

    <div class="form-group">
        <label>Service Name</label>
        <input type="text" name="service_name" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description"></textarea>
    </div>

    <div class="slotForm">
        <label>Time Slots</label>

        <div class="slot-inputs">
            <select name="hours[]" required>
                <?php for ($i = 1; $i <= 12; $i++) { ?>
                    <option value="<?= $i ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                <?php } ?>
            </select>

            <span>:</span>

            <select name="minutes[]" required>
                <option value="00">00</option>
                <option value="15">15</option>
                <option value="30">30</option>
                <option value="45">45</option>
            </select>

            <select name="ampms[]" required>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </div>
    </div>

    <button type="submit" class="save-btn">Save Service</button>
</form>

<script>
(function () {
    const form = document.getElementById("addServiceForm");
    if (!form) return;

    let submitting = false;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (submitting) return;
        submitting = true;

        const submitBtn = form.querySelector("button[type='submit']");
        if (submitBtn) submitBtn.disabled = true;

        fetch(window.location.href, {
            method: "POST",
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("✅ Service saved successfully!");
                location.reload();
            } else {
                submitting = false;
                if (submitBtn) submitBtn.disabled = false;
                alert("Error: " + data.message);
            }
        })
        .catch(() => {
            submitting = false;
            if (submitBtn) submitBtn.disabled = false;
            alert("An error occurred");
        });
    });
})();
</script>

