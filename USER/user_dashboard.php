<?php
// ==========================
// SESSION & DB CONNECTION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================
// TOTAL APPOINTMENTS
// ==========================
$q = mysqli_query($conn,
    "SELECT COUNT(*) AS total 
     FROM appointments 
     WHERE user_id='$user_id'"
);
$totalAppointments = mysqli_fetch_assoc($q)['total'] ?? 0;

// ==========================
// UPCOMING APPOINTMENTS
// ==========================
$q = mysqli_query($conn,
    "SELECT COUNT(*) AS upcoming 
     FROM appointments 
     WHERE user_id='$user_id'
     AND appointment_date >= CURDATE()
     AND status IN ('pending','confirmed')"
);
$upcomingAppointments = mysqli_fetch_assoc($q)['upcoming'] ?? 0;

// ==========================
// COMPLETED APPOINTMENTS
// ==========================
$q = mysqli_query($conn,
    "SELECT COUNT(*) AS completed 
     FROM appointments 
     WHERE user_id='$user_id'
     AND status='completed'"
);
$completedAppointments = mysqli_fetch_assoc($q)['completed'] ?? 0;

// ==========================
// PROFILE STATUS
// ==========================
$q = mysqli_query($conn,
    "SELECT status FROM users WHERE id='$user_id'"
);
$user = mysqli_fetch_assoc($q);
$profileStatus = ucfirst($user['status'] ?? 'inactive');

// ==========================
// NEXT APPOINTMENT
// ==========================
$q = mysqli_query($conn,
    "SELECT a.*, s.service_name
     FROM appointments a
     JOIN services s ON a.service_id = s.id
     WHERE a.user_id = '$user_id'
       AND a.status IN ('pending','confirmed')
       AND (
            a.appointment_date > CURDATE()
            OR (
                a.appointment_date = CURDATE()
                AND a.appointment_time >= CURTIME()
            )
       )
     ORDER BY a.appointment_date ASC, a.appointment_time ASC
     LIMIT 1"
);

$nextAppointment = mysqli_fetch_assoc($q);
?>

<!-- =======================
     DASHBOARD UI
======================= -->

<h1 class="dash-title">DASHBOARD</h1>

<div class="user-dash-wrapper">

    <!-- =======================
         TOP STATS
    ======================= -->
    <div class="user-stats">

        <div class="stat-card">
            <i class="bx bx-calendar-check"></i>
            <div>
                <p class="stat-label">Total Appointments</p>
                <h2 class="stat-value"><?= $totalAppointments ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <i class="bx bx-time"></i>
            <div>
                <p class="stat-label">Upcoming</p>
                <h2 class="stat-value"><?= $upcomingAppointments ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <i class="bx bx-check-circle"></i>
            <div>
                <p class="stat-label">Completed</p>
                <h2 class="stat-value"><?= $completedAppointments ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <i class="bx bx-user"></i>
            <div>
                <p class="stat-label">Profile Status</p>
                <h2 class="stat-value active"><?= $profileStatus ?></h2>
            </div>
        </div>

    </div>

    <!-- =======================
         MAIN CONTENT
    ======================= -->
    <div class="user-dash-main">

        <!-- NEXT APPOINTMENT -->
        <div class="card-box next-appoint">
            <h3 class="box-title">Next Appointment</h3>

            <?php if ($nextAppointment): ?>
                <div class="next-info">
                    <p><i class="bx bx-cut"></i> <?= $nextAppointment['service_name'] ?></p>
                    <p><i class="bx bx-calendar"></i>
                        <?= date('d M Y', strtotime($nextAppointment['appointment_date'])) ?>
                    </p>
                    <p><i class="bx bx-time"></i>
                        <?= date('h:i A', strtotime($nextAppointment['appointment_time'])) ?>
                    </p>
                </div>

                <span class="status <?= $nextAppointment['status'] ?>">
                    <?= ucfirst($nextAppointment['status']) ?>
                </span>
            <?php else: ?>
                <p style="color:#aaa;">No upcoming appointments</p>
            <?php endif; ?>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="card-box quick-actions">
            <h3 class="box-title">Quick Actions</h3>

           <button class="quick-btn" onclick="quickNav('./book_appointment.php')">
    Book Appointment
</button>

<button class="quick-btn" onclick="quickNav('./my_appointment.php')">
    My Appointments
</button>
<button class="quick-btn" onclick="quickNav('profile.php')">
    Profile
</button>

        </div>

    </div>

</div>
<script>
function quickNav(page) {
    // Block if profile incomplete
    if (isProfileComplete === 0 && page !== 'profile.php') {
        showProfileMessage();
        return;
    }

    // Remove old active
    document.querySelector(".sidebar .active")?.classList.remove("active");

    // Activate matching sidebar item
    document.querySelectorAll("[data-page]").forEach(item => {
        if (item.dataset.page === page) {
            item.classList.add("active");
        }
    });

    // Load page inside dashboard
    loadPage(page);
}
</script>
