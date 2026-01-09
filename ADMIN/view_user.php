<?php
require "../config.php";

/* ===============================
   BASIC VALIDATION
================================ */
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$user_id = intval($_GET['id']);

/* ===============================
   FETCH USER
================================ */
$userQ = mysqli_query($conn, "
    SELECT id, name, email, profile_completed, created_at
    FROM users
    WHERE id = $user_id
");

if (!$userQ) {
    die("User Query Failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($userQ);

if (!$user) {
    die("User not found");
}

/* ===============================
   FETCH USER PROFILE
================================ */
$profileQ = mysqli_query($conn, "
    SELECT phone, age, gender, profile_image
    FROM user_profiles
    WHERE user_id = $user_id
");

if (!$profileQ) {
    die("Profile Query Failed: " . mysqli_error($conn));
}

$profile = mysqli_fetch_assoc($profileQ);

/* ===============================
   PROFILE IMAGE
================================ */
$profileImg = (!empty($profile['profile_image']) && file_exists("../uploads/profiles/" . $profile['profile_image']))
    ? "../uploads/profiles/" . $profile['profile_image']
    : "../images/download.jpeg";
?>

<div class="user-id-card horizontal">

    <!-- LEFT : IMAGE + NAME + STATUS -->
    <div class="card-left">

        <div class="id-avatar">
            <img src="<?= htmlspecialchars($profileImg) ?>" alt="User">
        </div>

        <h2 class="id-name"><?= htmlspecialchars($user['name']) ?></h2>

        <span class="id-status <?= $user['profile_completed'] ? 'active' : 'inactive' ?>">
            <?= $user['profile_completed'] ? 'Active' : 'Inactive' ?>
        </span>

    </div>

    <!-- RIGHT : DETAILS -->
    <div class="card-right">

        <div class="id-details">
            <p><span>Email :</span><?= htmlspecialchars($user['email']) ?></p>
            <p><span>Phone :</span><?= htmlspecialchars($profile['phone'] ?? '-') ?></p>
            <p><span>Gender :</span><?= htmlspecialchars($profile['gender'] ?? '-') ?></p>
            <p><span>Age :</span><?= htmlspecialchars($profile['age'] ?? '-') ?></p>
            <p>
                <span>Joined :</span>
                <?= !empty($user['created_at']) 
                    ? date('d M Y', strtotime($user['created_at'])) 
                    : '-' ?>
            </p>
        </div>

    </div>

</div>


<style>

.user-id-card.horizontal {
    width: 650px;
    padding: 25px;
    background: #050b10;
    border-radius: 22px;
    border: 2px solid #00ffcc;
    box-shadow: 0 0 30px rgba(0,255,204,0.25);
    color: #fff;

    display: flex;
    align-items: center;
    gap: 30px;
}

/* LEFT */
.card-left {
    flex: 0 0 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

/* IMAGE */
.id-avatar {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    border: 4px solid #00ffcc;
    overflow: hidden;
    box-shadow: 0 0 25px rgba(0,255,204,0.4);
}

.id-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* NAME */
.id-name {
    margin-top: 12px;
    font-size: 20px;
    font-weight: 600;
}

/* STATUS */
.id-status {
    margin-top: 6px;
    padding: 6px 18px;
    border-radius: 20px;
    font-size: 13px;
    display: inline-block;
}

.id-status.active {
    background: #00ff88;
    color: #000;
}

.id-status.inactive {
    background: #ff4444;
    color: #fff;
}

/* RIGHT */
.card-right {
    flex: 1;
}

/* DETAILS */
.id-details p {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    margin: 6px 0;
    background: #08131a;
    border-radius: 8px;
    font-size: 14px;
}

.id-details span {
    color: #8fffea;
}


</style>