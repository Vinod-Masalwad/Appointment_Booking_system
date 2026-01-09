<?php
session_start();
require "../config.php";

// 1. SECURITY & CONFIG
$admin_id = $_SESSION['admin_id'] ?? 0;
if ($admin_id == 0) {
    die("Access Denied: Please log in.");
}



/* ===============================
   2. HANDLE AJAX SAVE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_save'])) {
    header("Content-Type: application/json");

    $phone  = $_POST['phone'] ?? '';
    $age    = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Fetch existing image for fallback
    $stmt = $conn->prepare("SELECT profile_image FROM admin_profiles WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();

    $img = $existing['profile_image'] ?? null;

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $new_name = time() . "_" . bin2hex(random_bytes(4)) . "." . $file_ext;
           if (move_uploaded_file(
    $_FILES['profile_image']['tmp_name'],
    "../uploads/profiles/" . $new_name
)) {
    $img = $new_name;
}

        }
    }

    // Update or Insert into admin_profiles
    if ($existing) {
        $sql = "UPDATE admin_profiles SET phone=?, age=?, gender=?, profile_image=? WHERE admin_id=?";
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param("ssssi", $phone, $age, $gender, $img, $admin_id);
    } else {
        $sql = "INSERT INTO admin_profiles (admin_id, phone, age, gender, profile_image) VALUES (?, ?, ?, ?, ?)";
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param("issss", $admin_id, $phone, $age, $gender, $img);
    }

    if ($update_stmt->execute()) {
        // Mark profile as completed in main table
        $conn->query("UPDATE admins SET profile_completed=1 WHERE id='$admin_id'");
        echo json_encode(["status" => "success", "message" => "Profile updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    exit;
}

/* ===============================
   3. FETCH DATA FOR VIEW
================================ */
$userQ = mysqli_query($conn, "SELECT * FROM admins WHERE id='$admin_id'");
$admins  = mysqli_fetch_assoc($userQ);

$profileQ = mysqli_query($conn, "SELECT * FROM admin_profiles WHERE admin_id='$admin_id'");
$profile  = mysqli_fetch_assoc($profileQ);
?>

<!-- HTML Content -->
<h1 class="dash-title">PROFILE</h1>

<form id="profileForm" class="profile-wrapper" enctype="multipart/form-data">
    <!-- LEFT -->
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
        <h2 class="profile-name"><?= htmlspecialchars($admins['name'] ?? 'Admin') ?></h2>
    </div>

    <!-- RIGHT -->
    <div class="profile-right">
        <div class="info-row">
            <div class="card-box">
                <h3 class="box-title">Personal Information</h3>
                <div class="info-list">
                    <p><span>Email:</span> <?= htmlspecialchars($admins['email'] ?? '-') ?></p>
                    <p><span>Phone:</span> <?= htmlspecialchars($profile['phone'] ?? '-') ?></p>
                    <p><span>Gender:</span><?= htmlspecialchars($profile['gender'] ?? '-') ?></p>
                </div>
            </div>

            <div class="card-box">
                <h3 class="box-title">Account Details</h3>
                <div class="info-list">
                    <p><span>Role:</span> Admin</p>
                    <p>
                        <span>Status:</span>
                        <span class="<?= ($admins['profile_completed'] ?? 0) ? 'Active' : 'InActive' ?>">
                            <?= ($admins['profile_completed'] ?? 0) ? 'Active' : 'InActive' ?>
                        </span>
                    </p>
                    <p><span>Joined:</span>
                        <?= !empty($admins['created_at']) ? date('d M Y', strtotime($admins['created_at'])) : '-' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- EDIT FORM -->
        <div class="card-box edit-box">
            <h3 class="box-title">Edit Profile</h3>
            <div class="edit-grid">
                <input type="text" name="phone" placeholder="Phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                <input type="number" name="age" placeholder="Age" value="<?= htmlspecialchars($profile['age'] ?? '') ?>">
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male"   <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other"  <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>

                <button type="submit" class="save-btn" id="saveBtn">
                    <span id="btnText">Save Changes</span>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// 1. Image Preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 2. AJAX Submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    const btnText = document.getElementById('btnText');
    const formData = new FormData(this);
    

    formData.append('ajax_save', '1');

  
    btn.disabled = true;
    btnText.innerText = "Saving...";

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); 
        } else {
            alert("Error: " + data.message);
        }
    })
    
    .finally(() => {
        btn.disabled = false;
        btnText.innerText = "Save Changes";
    });
});
</script>