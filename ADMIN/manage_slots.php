    <?php
    session_start();
    require "../config.php";

    if (!isset($_GET['service_id'])) {
        header("Location: services.php");
        exit;
    }



    /* ===========================================================
    DATA FETCHING
    =========================================================== */
    if (!isset($_GET['service_id'])) exit("Missing Service ID");
    $service_id = (int)$_GET['service_id'];

    // Get Service Name
    $res = mysqli_query($conn, "SELECT service_name FROM services WHERE id=$service_id");
    $service = mysqli_fetch_assoc($res);

    // Get Existing Slots
    $slots = mysqli_query($conn, "SELECT * FROM service_time_slots WHERE service_id=$service_id ORDER BY slot_time ASC");
    ?>

 
    <link rel="stylesheet" href="../service.css">

    <div class="slot-wrapper">
        <h3><?= htmlspecialchars($service['service_name'] ?? 'Service') ?> – Slots</h3>

        <!-- ADD SLOT FORM -->
        <form id="slotForm" action="./add_slots.php" method="POST">
            <!-- Send these via AJAX -->
            <input type="hidden" name="service_id" value="<?= $service_id ?>">

            <select name="hour" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                <?php endfor; ?>
            </select>

            <select name="minute" required>
                <option value="00">00</option>
                <option value="15">15</option>
                <option value="30">30</option>
                <option value="45">45</option>
            </select>

            <select name="ampm" required>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>

            <button type="submit" name="add_slot">Add Slot</button>

        </form>

        <hr>

        <!-- SLOT LIST -->
        <div class="slot-list">
            <?php if (mysqli_num_rows($slots) > 0): ?>
                <?php while ($s = mysqli_fetch_assoc($slots)): ?>
                    <div class="slot-item">
                        <span class="slot-time">
                            <?= date('h:i A', strtotime($s['slot_time'])) ?>
                        </span>
                        <a href="./delete_slots.php?id=<?= $s['id'] ?>&service_id=<?= $service_id ?>"
    onclick="return confirm('Delete this slot?')" class="delete-btn">
    Delete
    </a>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No slots added yet.</p>
            <?php endif; ?>
        </div>
    </div>