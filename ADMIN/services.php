<?php
session_start();
require "../config.php";

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

/* ===== FETCH SERVICES ===== */
$services = mysqli_query($conn, "
    SELECT MIN(id) AS id, service_name, description
    FROM services
    WHERE is_active = 1
    GROUP BY service_name
    ORDER BY created_at DESC
");


if (!$services) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<h1 class="dash-title">SERVICES</h1>

<div class="services-wrapper">

    <!-- ADD SERVICE -->
    <button class="add-service-btn" id="addServiceBtn">+ Add New Service</button>

    <!-- SERVICES GRID -->
    <div class="services-grid">

        <?php if (mysqli_num_rows($services) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($services)) { ?>
                <div class="service-card">

                    <div class="service-icon">
                        <i class='bx bx-briefcase'></i>
                    </div>

                    <h2 class="service-name">
                        <?= htmlspecialchars($row['service_name']) ?>
                    </h2>

                    <p class="service-desc">
                        <?= htmlspecialchars($row['description'] ?? 'No description') ?>
                    </p>

                    <div class="service-actions">
    <button class="edit-btn" onclick="openEditService(<?= $row['id'] ?>)">Edit</button>

    <button class="slot-btn" onclick="openSlots(<?= $row['id'] ?>)">
        Slots
    </button>

    <button class="delete-btn" onclick="deleteService(<?= $row['id'] ?>)">Delete</button>
</div>


                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No services found</p>
        <?php } ?>

    </div>
</div>

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="serviceModal" style="display:none;">
    <div class="modal-box">
        <span class="close-modal" id="closeModal">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>

<script>
/* ===== OPEN ADD SERVICE MODAL ===== */
document.getElementById("addServiceBtn").onclick = () => {
    fetch("add_service.php")
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("serviceModal").style.display = "flex";
        });
};

/* ===== CLOSE MODAL ===== */
document.getElementById("closeModal").onclick = () => {
    document.getElementById("serviceModal").style.display = "none";
};

/* ===== DELETE SERVICE ===== */
function deleteService(id) {
    if (!confirm("Delete this service?")) return;

    fetch("delete_service.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Service Deleted Successfully");
            location.reload();
        } else {
            alert(data.message || "Delete failed");
        }
    })
    .catch(() => alert("Something went wrong"));
}

/* ===== OPEN EDIT SERVICE ===== */
function openEditService(id) {
    fetch("edit_service.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("serviceModal").style.display = "flex";
        });
}

/* ===== DELEGATED AJAX SUBMIT (ADD + EDIT) ===== */
document.addEventListener("submit", function (e) {

    /* ADD SERVICE */
    if (e.target.id === "addServiceForm") {
        e.preventDefault();

        fetch("add_service.php", {
            method: "POST",
            body: new FormData(e.target)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("Service Added Successfully");
                document.getElementById("serviceModal").style.display = "none";
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    /* EDIT SERVICE */
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
        });
    }
});
</script>

<script>
function openEditService(id) {
    fetch("edit_service.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("serviceModal").style.display = "flex";

            const form = document.getElementById("editServiceForm");

            form.addEventListener("submit", function (e) {
                e.preventDefault();

                fetch("edit_service.php", {
                    method: "POST",
                    body: new FormData(form)
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
            });
        });
}
</script>

<script>
function openSlots(serviceId) {
    fetch("manage_slots.php?service_id=" + serviceId)
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("serviceModal").style.display = "flex";
        });
}
</script>
