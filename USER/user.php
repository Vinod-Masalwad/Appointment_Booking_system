<?php
session_start();
require "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch User and Profile Image Status
$query = mysqli_query($conn, "
    SELECT u.name, u.profile_completed, p.profile_image 
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    WHERE u.id = '$user_id'
");
$user = mysqli_fetch_assoc($query);

$username = $user['name'] ?? 'User';
$profileCompleted = (int)($user['profile_completed'] ?? 0);
$profileImg = (!empty($user['profile_image']) && file_exists("../uploads/profiles/" . $user['profile_image']))
    ? "../uploads/profiles/" . $user['profile_image']
    : "../images/download.jpeg";


?>
<!DOCTYPE html>
<html>
<head>
    <title>USER PANEL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anta&family=Audiowide&family=Lexend:wght@100..900&family=Racing+Sans+One&family=Science+Gothic:wght@100..900&display=swap">

    <!-- MATERIAL ICONS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../user.css">
</head>
<body class="user-panel">

<nav>
        <div class="logo">APPOINTMENT BOOKING SYSTEM</div>
        <div class="hamburger" onclick="toggleMenu()">☰</div>
        
    </nav>

<div class="home_contianer">
    <aside class="sidebar">
        <div class="logo-section">
            <img src="<?= $profileImg ?>" class="logo1" id="sidebarImg">
            <span class="logo-text1"> <?= htmlspecialchars(ucfirst(strtolower($username))) ?></span>
        </div>

        <div class="toggle-btn" id="toggleBtn"><i class='bx bx-chevron-left'></i></div>

        <div class="menu">
            <ul>
                
                <li data-page="./user_dashboard.php">
                    <i class="bx bxs-dashboard"></i> <span class="text">Dashboard</span>
                </li>
                <li data-page="./book_appointment.php">
                    <i class="bx bxs-calendar-plus"></i> <span class="text">Book Appointment</span>
                </li>
                <li data-page="./my_appointment.php">
                    <i class="bx bxs-time-five"></i> <span class="text">My Appointments</span>
                </li>
            </ul>
        </div>

        <div class="bottom">

            <div class="profile" data-page="settings.php">
                <i class="bx bx-cog"></i>
                <span class="text">Settings</span>
            </div>

            <div class="profile" data-page="profile.php">
                <i class="bx bxs-user-circle"></i>
                <span class="text">Profile</span>
            </div>
            

            <div class="profile" data-page="logout.php">
                <i class="bx bx-log-out"></i>
                <span class="text">Logout</span>
            </div>
        </div>
    </aside>

    <main class="content-area" id="content-area"></main>
</div>

<script>
// --- Global State ---
let isProfileComplete = <?= $profileCompleted ?>;
const contentArea = document.getElementById("content-area");

// --- 1. Page Loader---
function loadPage(page) {
    fetch(page)
        .then(res => res.text())
        .then(html => {
            contentArea.innerHTML = html;
            const scripts = contentArea.querySelectorAll("script");
            scripts.forEach(oldScript => {
                const newScript = document.createElement("script");
                newScript.text = oldScript.text;
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            });
        })
        .catch(() => contentArea.innerHTML = "<h3>Error loading page</h3>");
}

// --- 2. Navigation Logic ---
function initNavigation() {
    const items = document.querySelectorAll(".menu li, .bottom .settings, .bottom .profile");

    // Initial State
    if (isProfileComplete === 0) {
        lockMenu();
        showProfileMessage();
    } else {
        const dashBtn = document.querySelector('[data-page="./user_dashboard.php"]');
        dashBtn?.classList.add("active");
        loadPage("./user_dashboard.php");
    }

    // Click Handling
    items.forEach(item => {
        item.onclick = () => {
            if (item.classList.contains("locked")) {
                showProfileMessage();
                return;
            }
            document.querySelector(".sidebar .active")?.classList.remove("active");
            item.classList.add("active");
            if (item.dataset.page) loadPage(item.dataset.page);
        };
    });
}

function lockMenu() {
    document.querySelectorAll(".menu li, .bottom .settings").forEach(el => {
        // Dashboard is usually unlocked but shows the message
        if (el.dataset.page !== "./user_dashboard.php") el.classList.add("locked");
    });
}

function showProfileMessage() {
    contentArea.innerHTML = `
        <div class="profile-warning">
            <h2 class="dash-title">WELCOME</h2>
            <p class="welcome-text">Please complete your profile to unlock all features.</p>
            <button class="save-btn" onclick="triggerProfileLoad()">Complete Profile</button>
        </div>`;
}

function triggerProfileLoad() {
    document.querySelector(".sidebar .active")?.classList.remove("active");
    document.querySelector(".profile").classList.add("active");
    loadPage("profile.php");
}

// --- 3. Global Form Submission ---
document.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "profileForm") {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector(".save-btn");
        const formData = new FormData(form);
        formData.append("ajax_save", "1");

        btn.innerText = "Saving...";
        btn.disabled = true;

        fetch("profile.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    alert("✅ Success: Profile Updated!");
                    location.reload(); 
                } else {
                    alert("Error: " + data.message);
                    btn.innerText = "Save Changes";
                    btn.disabled = false;
                }
            })
            .catch(err => {
                if (btn.disabled) location.reload(); 
            });
    }
});

document.addEventListener("DOMContentLoaded", initNavigation);
document.getElementById('toggleBtn').onclick = () => document.querySelector('.sidebar').classList.toggle('collapsed');



window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

   if (status === 'success') {
    alert("📩 Request sent for admin approval");
} else if (status === 'exists') {
    alert("❌ Slot already taken. Try another 😕");
} else if (status === 'error') {
    alert("⚠️ Something went wrong. Try again 😥");
}

    if (status) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}
</script>
</body>
</html>