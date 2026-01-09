<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

/* SERVICES FOR FILTER */
$services = mysqli_query($conn, "SELECT id, service_name FROM services WHERE is_active=1");
?>

<h1 class="dash-title">APPOINTMENTS</h1>

<div class="appt-container">

    <!-- ===== FILTERS ===== -->
    <div class="card-box">
        <h3 class="box-title">Filters</h3>

        <div class="filter-grid">

            <select id="status" class="filter-input">
                <option value="">Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Approved</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select id="date" class="filter-input">
                <option value="">Date</option>
                <option value="today">Today</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="week">This Week</option>
            </select>

            <select id="service" class="filter-input">
                <option value="">Service Type</option>
                <?php while ($s = mysqli_fetch_assoc($services)) { ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['service_name']) ?>
                    </option>
                <?php } ?>
            </select>

        </div>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="appt-table card-box" id="appointment-area">
        <h3 class="box-title">All Appointments</h3>

        <!-- SCROLL AREA -->
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="appointmentData">
                    <!-- AJAX DATA LOADS HERE -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== AJAX SCRIPT ===== -->
<script>
function loadAppointments() {
    const status  = document.getElementById('status').value;
    const date    = document.getElementById('date').value;
    const service = document.getElementById('service').value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_appointments.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        document.getElementById("appointmentData").innerHTML = this.responseText;
    };

    xhr.send(
        "status=" + encodeURIComponent(status) +
        "&date=" + encodeURIComponent(date) +
        "&service=" + encodeURIComponent(service)
    );
}

/* INITIAL LOAD */
loadAppointments();

/* LIVE FILTER */
document.getElementById('status').addEventListener('change', loadAppointments);
document.getElementById('date').addEventListener('change', loadAppointments);
document.getElementById('service').addEventListener('change', loadAppointments);
</script>
