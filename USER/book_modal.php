<?php
session_start();
require "../config.php";
$user_id    = $_SESSION['user_id'] ?? 0;
$service_id = $_GET['service_id'] ?? 0;

$service_q = mysqli_query($conn, "SELECT service_name FROM services WHERE id = $service_id");
$service = mysqli_fetch_assoc($service_q);
?>

<h2 class="modal-title"><?= htmlspecialchars($service['service_name']) ?></h2>

<form method="POST" class="modal-form" action="add_appointment.php">
    <input type="hidden" name="service_id" id="service_id" value="<?= $service_id ?>">

    <div class="form-group">
        <label>Select Date</label>
        <input 
            type="date" 
            name="appointment_date" 
            id="appointment_date"
            min="<?= date('Y-m-d') ?>" 
            required
            onchange="fetchAvailableSlots()" 
        >
    </div>

    <div class="form-group">
        <label>Select Slot</label>
        <select name="appointment_time" id="appointment_time" required disabled>
            <option value="">-- Pick a date first --</option>
        </select>
    </div>

    <button type="submit" class="save-btn">Book Appointment</button>
</form>