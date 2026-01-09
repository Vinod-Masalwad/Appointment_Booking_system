<?php
session_start();
require "../config.php";

$user_id = $_SESSION['user_id'] ?? 0;

/* ===============================
   1. HANDLE AJAX SAVE
================================ */

if (isset($_POST['save_profile']) || isset($_POST['ajax_save'])) {
    header("Content-Type: application/json");

    $phone  = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $age    = mysqli_real_escape_string($conn, $_POST['age'] ?? '');
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');

    $profileQ = mysqli_query($conn, "SELECT profile_image FROM user_profiles WHERE user_id='$user_id'");
    $existing = mysqli_fetch_assoc($profileQ);

    if (!empty($_FILES['profile_image']['name'])) {
        $img = time() . "_" . $_FILES['profile_image']['name'];
        move_uploaded_file($_FILES['profile_image']['tmp_name'],"../uploads/profiles/" . $img);

    } else {
        $img = $existing['profile_image'] ?? null;
    }

    if ($existing) {
        $query = "UPDATE user_profiles 
                  SET phone='$phone', age='$age', gender='$gender', profile_image='$img' 
                  WHERE user_id='$user_id'";
    } else {
        $query = "INSERT INTO user_profiles (user_id, phone, age, gender, profile_image)
                  VALUES ('$user_id','$phone','$age','$gender','$img')";
    }

    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE users SET profile_completed=1 WHERE id='$user_id'");
        echo json_encode(["status" => "success", "message" => "Profile updated!"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

/* ===============================
   2. FETCH DATA
================================ */
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user  = mysqli_fetch_assoc($userQ);

$profileQ = mysqli_query($conn, "SELECT * FROM user_profiles WHERE user_id='$user_id'");
$profile  = mysqli_fetch_assoc($profileQ);
?>

<h1 class="dash-title">PROFILE</h1>

<form id="profileForm" class="profile-wrapper" method="POST" enctype="multipart/form-data">

    <!-- LEFT COLUMN -->
    <div class="profile-left card-box">
        <div class="profile-photo-wrapper">
            <img
                src="<?= !empty($profile['profile_image']) ? '../uploads/profiles/'.$profile['profile_image'] : '../images/download.jpeg' ?>"
                class="profile-photo"
                id="profilePreview">

            <label for="profile_image" class="camera-btn">
                <i class="bx bx-camera"></i>
            </label>

            <input type="file" id="profile_image" name="profile_image" hidden onchange="previewImage(this)">
        </div>

        <h2 class="profile-name"><?= htmlspecialchars($user['name'] ?? '-') ?></h2>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="profile-right">

        <!-- TOP ROW: Two Boxes Side by Side -->
        <div class="info-row">
            <div class="card-box">
                <h3 class="box-title">Personal Information</h3>
                <div class="info-list">
                    <p><span>Email:</span> <?= htmlspecialchars($user['email'] ?? '-') ?></p>
                    <p><span>Phone:</span> <?= htmlspecialchars($profile['phone'] ?? '-') ?></p>
                    <p><span>Gender:</span> <?= htmlspecialchars($profile['gender'] ?? '-') ?></p>
                </div>
            </div>

            <div class="card-box">
                <h3 class="box-title">Account Details</h3>
                <div class="info-list">
                    <p><span>Role:</span> User</p>

                    <p>
                        <span>Status:</span>
                        <span class="<?= ($user['profile_completed'] ?? 0) ? 'Active' : 'InActive' ?>">
                            <?= ($user['profile_completed'] ?? 0) ? 'Active' : 'InActive' ?>
                        </span>
                    </p>

                    <p><span>Joined:</span>
                        <?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- BOTTOM BOX -->
        <div class="card-box edit-box">
            <h3 class="box-title">Edit Profile</h3>

            <div class="edit-grid">
                <input type="text" name="phone" placeholder="Phone"
                       value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">

                <input type="number" name="age" placeholder="Age"
                       value="<?= htmlspecialchars($profile['age'] ?? '') ?>">

                <select name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male"   <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other"  <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>

                <button type="submit" name="save_profile" class="save-btn">
                    Save
                </button>
            </div>
        </div>

    </div>
</form>

<script>

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>