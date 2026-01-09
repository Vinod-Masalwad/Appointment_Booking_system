<?php
session_start();
require "../config.php";

/* =========================
   BASIC ADMIN CHECK
========================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

/* =========================
   DASHBOARD COUNTS
========================= */

/* Total Appointments */
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments");
$totalAppointments = mysqli_fetch_assoc($q1)['total'];

/* Today Appointments */
$q2 = mysqli_query($conn, "
    SELECT COUNT(*) AS today 
    FROM appointments 
    WHERE appointment_date = CURDATE()
");
$todayAppointments = mysqli_fetch_assoc($q2)['today'];

/* Pending Appointments */
$q3 = mysqli_query($conn, "
    SELECT COUNT(*) AS pending 
    FROM appointments 
    WHERE status='pending'
");
$pendingAppointments = mysqli_fetch_assoc($q3)['pending'];

/* Total Users */
$q4 = mysqli_query($conn, "SELECT COUNT(*) AS users FROM users");
$totalUsers = mysqli_fetch_assoc($q4)['users'];

/* =========================
   RECENT APPOINTMENTS
========================= */
$recent = mysqli_query($conn, "
    SELECT 
        a.appointment_date,
        a.appointment_time,
        u.name AS user_name,
        s.service_name,
        a.status
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    ORDER BY a.created_at DESC
    LIMIT 4
");

?>


<h1 class="dash-title">DASHBOARD</h1>

<div class="dashboard-wrapper">

    <!-- ===== TOP CARDS ===== -->
    <div class="user-stats">

        <div class="stat-card">
            <i class="bx bx-calendar-check"></i>
            <span class="card-title">Total Appointments</span>
            <span class="card-value"><?= $totalAppointments ?></span>
        </div>

        <div class="stat-card">
            <i class='bx bxs-calendar-check'></i>
            <span class="card-title">Today</span>
            <span class="card-value"><?= $todayAppointments ?></span>
        </div>

        <div class="stat-card">     
            <i class='bx bxs-hourglass'></i>
            <span class="card-title">Pending</span>
            <span class="card-value"><?= $pendingAppointments ?></span>
        </div>

        <div class="stat-card">
            <i class='bx bxs-user-detail'></i>
            <span class="card-title">Users</span>
            <span class="card-value"><?= $totalUsers ?></span>
        </div>

    </div>


    <!-- ===== RECENT APPOINTMENTS ===== -->
<div class="recent-wrapper card-box">

    <h3 class="rec">Recent Appointments</h3>

    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            <?php if (mysqli_num_rows($recent) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($recent)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['service_name']) ?></td>
                        <td><?= date("d M Y", strtotime($row['appointment_date'])) ?></td>
                    <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
                        <td>
                            
                            <span class="status <?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="3">No recent appointments</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>

