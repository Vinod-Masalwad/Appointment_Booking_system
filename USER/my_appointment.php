<?php
session_start();
require "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* ===== FETCH USER APPOINTMENTS ===== */
$query = mysqli_query($conn, "
    SELECT 
        s.service_name,
        a.appointment_date,
        a.appointment_time,
        a.status
    FROM appointments a
    JOIN services s ON s.id = a.service_id
    WHERE a.user_id = $user_id
    ORDER BY a.created_at DESC
");


if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<h1 class="dash-title">MY APPOINTMENTS</h1>

<div class="my-appoint-wrapper">
    <div class="my-appoint-grid">

        <?php if (mysqli_num_rows($query) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($query)) { ?>

                <div class="my-appoint-card">
                    <div class="card-top">
                        <span class="service">
                            <?= htmlspecialchars($row['service_name']) ?>
                        </span>

                        <span class="status <?= htmlspecialchars($row['status']) ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </div>

                    <div class="card-info">
                        <p>
                            <i class="bx bx-calendar"></i>
                            <?= date("d M Y", strtotime($row['appointment_date'])) ?>
                        </p>

                        <p>
                            <i class="bx bx-time"></i>
                            <?= date("h:i A", strtotime($row['appointment_time'])) ?>
                        </p>
                    </div>
                </div>

            <?php } ?>

        <?php } else { ?>

    
            <div class="no-appointment">
                <i class="bx bx-calendar-x"></i>
                <p>No appointments booked yet</p>
                <span>Book a service to see it here</span>
            </div>

        <?php } ?>

    </div>
</div>
