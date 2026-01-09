<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* FETCH ACTIVE SERVICES */
$services = mysqli_query($conn, "
    SELECT id, service_name, description
    FROM services 
    WHERE is_active = 1
    ORDER BY created_at DESC
");
?>

<h1 class="dash-title">BOOK APPOINTMENT</h1>

<div class="appoint-grid">

<?php if (mysqli_num_rows($services) > 0) { ?>

    <?php while ($s = mysqli_fetch_assoc($services)) { ?>
        <div class="appoint-card">
            <i class="bx bx-cut icon"></i>

            <span class="box-label">
                <?= htmlspecialchars($s['service_name']) ?>
            </span>

            <p class="service-desc">
                <?= htmlspecialchars($s['description']) ?>
            </p>

            <button 
                class="appoint-btn"
                onclick="openBookingModal(<?= (int)$s['id'] ?>)">
                Book
            </button>
        </div>
    <?php } ?>

<?php } else { ?>

    <!-- NO SERVICE / NO APPOINTMENT STATE -->
    <div class="no-appointment">
        <i class="bx bx-calendar-x"></i>
        <p>No appointment services available right now</p>
        <span>Please check back later</span>
    </div>

<?php } ?>

</div>


<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="bookingModal" style="display:none;">
    <div class="modal-box">
        <span class="close-modal" onclick="closeBookingModal()">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>

<script>
function openBookingModal(serviceId) {
    fetch("./book_modal.php?service_id=" + serviceId)
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("bookingModal").style.display = "flex";
        });
}

function closeBookingModal() {
    document.getElementById("bookingModal").style.display = "none";
}

function openBookingModal(serviceId) {
    
    fetch("./book_modal.php?service_id=" + serviceId + "&t=" + Date.now())
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            document.getElementById("bookingModal").style.display = "flex";
        });
}

function closeBookingModal() {
    document.getElementById("bookingModal").style.display = "none";
}


function fetchAvailableSlots() {
    const serviceIdEl = document.getElementById('service_id');
    const dateEl = document.getElementById('appointment_date');
    const slotDropdown = document.getElementById('appointment_time');

    if (!serviceIdEl || !dateEl || !slotDropdown) return;

    const serviceId = serviceIdEl.value;
    const date = dateEl.value;

    if (!date) return;

    console.log("Fetching slots for Date:", date, "Service:", serviceId); 


    fetch("./slot_availibility.php?service_id=" + serviceId + "&date=" + date)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text();
        })
        .then(html => {
            slotDropdown.innerHTML = html;
            slotDropdown.disabled = false; 
        })
        .catch(err => {
            console.error("Error:", err);
            alert("Could not load slots. Check console for error.");
        });
}
</script>