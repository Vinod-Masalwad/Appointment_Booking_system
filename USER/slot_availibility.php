<?php
require "../config.php";

date_default_timezone_set("Asia/Kolkata");

$service_id = intval($_GET['service_id'] ?? 0);
$date = $_GET['date'] ?? '';

if ($service_id > 0 && !empty($date)) {

    $today = date("Y-m-d");
    $currentTime = date("H:i:s");

   
    $timeCondition = "";
    if ($date === $today) {
        $timeCondition = "AND slot_time > '$currentTime'";
    }

    $query = "
        SELECT slot_time 
        FROM service_time_slots 
        WHERE service_id = $service_id 
          AND is_active = 1
          $timeCondition
          AND slot_time NOT IN (
              SELECT appointment_time 
              FROM appointments 
              WHERE service_id = $service_id 
                AND appointment_date = '$date'
          )
        ORDER BY slot_time ASC
    ";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<option value="">-- Select Slot --</option>';

        while ($slot = mysqli_fetch_assoc($result)) {
            $formatted_time = date("h:i A", strtotime($slot['slot_time']));
            echo "<option value='{$slot['slot_time']}'>{$formatted_time}</option>";
        }
    } else {
        echo '<option value="">No slots available</option>';
    }

} else {
    echo '<option value="">Select a valid date</option>';
}
?>
