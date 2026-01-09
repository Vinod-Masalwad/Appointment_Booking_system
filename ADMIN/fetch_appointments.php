<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    exit;
}

$status  = $_POST['status']  ?? '';
$date    = $_POST['date']    ?? '';
$service = $_POST['service'] ?? '';

$where = "WHERE 1";

/* STATUS */
if ($status != '') {
    $where .= " AND a.status='$status'";
}

/* DATE */
if ($date == 'today') {
    $where .= " AND a.appointment_date = CURDATE()";
}
if ($date == 'tomorrow') {
    $where .= " AND a.appointment_date = CURDATE() + INTERVAL 1 DAY";
}
if ($date == 'week') {
    $where .= " 
        AND WEEK(a.appointment_date,1)=WEEK(CURDATE(),1)
        AND YEAR(a.appointment_date)=YEAR(CURDATE())
    ";
}

/* SERVICE */
if ($service != '') {
    $service = (int)$service;
    $where .= " AND a.service_id=$service";
}

/* FETCH */
$q = mysqli_query($conn, "
    SELECT 
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.name AS user_name,
        s.service_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    $where
    ORDER BY a.created_at ASC
");

if (mysqli_num_rows($q) == 0) {
    echo "<tr><td colspan='7' style='text-align:center;'>No appointments found</td></tr>";
    exit;
}

while ($row = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td>#APT<?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['user_name']) ?></td>
    <td><?= htmlspecialchars($row['service_name']) ?></td>
    <td><?= date("d M Y", strtotime($row['appointment_date'])) ?></td>
    <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
    <td>
        <span class="status <?= $row['status'] ?>">
            <?= ucfirst($row['status']) ?>
        </span>
    </td>
    <td>
        <?php if ($row['status'] == 'pending') { ?>
            <a href="approve.php?id=<?= $row['id'] ?>" class="table-btn approve">Approve</a>
            <a href="reject.php?id=<?= $row['id'] ?>" class="table-btn delete">Reject</a>
        <?php } ?>

        <?php if ($row['status'] == 'confirmed') { ?>
            <a href="complete.php?id=<?= $row['id'] ?>" class="table-btn complete">Complete</a>
        <?php } ?>
    </td>
</tr>
<?php } ?>
