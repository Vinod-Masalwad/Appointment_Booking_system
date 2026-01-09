<?php
session_start();
require "../config.php";

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

/* ===== FETCH USERS ===== */
$users = mysqli_query($conn, "
    SELECT 
        u.id,
        u.name, 
        u.profile_completed,
        up.profile_image
    FROM users u
    LEFT JOIN user_profiles up ON up.user_id = u.id
    ORDER BY u.id DESC
");

if (!$users) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<h1 class="dash-title">USERS</h1>

<div class="user-wrapper">

   

    <!-- USER GRID -->
    <div class="user-grid">

        <?php while ($row = mysqli_fetch_assoc($users)) { ?>

            <?php
                /* ===== PROFILE IMAGE PATH ===== */
               $profileImg = (!empty($row['profile_image']) && file_exists("../uploads/profiles/" . $row['profile_image']))
    ? "../uploads/profiles/" . $row['profile_image']
    : "../images/download.jpeg";


            ?>

            <div class="user-card card-box">

                <img src="<?php echo htmlspecialchars($profileImg); ?>" class="user-photo">

                <h2 class="user-name"><?php echo htmlspecialchars($row['name']); ?></h2>

                <?php if ($row['profile_completed'] == 1) { ?>
                    <span class="user-status active">Active</span>
                <?php } else { ?>
                    <span class="user-status inactive">Inactive</span>
                <?php } ?>

                <div class="user-actions">
                    <button class="btn-view" onclick="openUserModal(<?= $row['id'] ?>)">View</button>
                    <button class="btn-delete" onclick="deleteUser(<?= $row['id'] ?>)">Delete</button>
                </div>

            </div>

        <?php } ?>

    </div>

</div>
<!-- ===== USER VIEW MODAL ===== -->
<div class="modal-overlay" id="userViewModal" style="display:none;">
    <div class="modal-box">
        <span class="close-modal" onclick="closeUserModal()">×</span>

        <h3 class="dash-title">User Details</h3>

        <div id="userViewContent">
            Loading...
        </div>
    </div>
</div>

<script>
function openUserModal(userId) {
    document.getElementById("userViewModal").style.display = "flex";
    document.getElementById("userViewContent").innerHTML = "Loading...";

    fetch("view_user.php?id=" + userId)
        .then(res => res.text())
        .then(data => {
            document.getElementById("userViewContent").innerHTML = data;
        });
}

function closeUserModal() {
    document.getElementById("userViewModal").style.display = "none";
}

function deleteUser(userId) {
    if (confirm("⚠️ Are you sure?\n\nThis user will be permanently deleted ❌")) {
        window.location.href = "delete_user.php?id=" + userId;
    }
}
</script>


