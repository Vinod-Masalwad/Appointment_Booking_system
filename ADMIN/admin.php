<?php
session_start();
require "../config.php";

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

/* ===== FETCH ADMIN DATA ===== */
$q = mysqli_query($conn, "
    SELECT a.name, a.profile_completed, p.profile_image 
    FROM admins a
    LEFT JOIN admin_profiles p ON a.id = p.admin_id
    WHERE a.id = '$admin_id'
");

$admins = mysqli_fetch_assoc($q);

$adminName = $admins['name'] ?? 'Admin';
$profileCompleted = (int)($admins['profile_completed'] ?? 0);


$profileImg = (!empty($admins['profile_image']) && file_exists("../uploads/profiles/" . $admins['profile_image']))
    ? "../uploads/profiles/" . $admins['profile_image']
    : "../images/download.jpeg";

?>

<!DOCTYPE html>
<html>
<head>
    <title>ADMIN PANEL</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anta&family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anta&family=Audiowide&family=Lexend:wght@100..900&family=Racing+Sans+One&family=Science+Gothic:wght@100..900&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../style.css">
    
</head>

<body class="user-panel">

<nav>
        <div class="logo">APPOINTMENT BOOKING SYSTEM</div>
        <div class="hamburger" onclick="toggleMenu()">☰</div>
        
    </nav>

<div class="home_contianer">
    <!-- ===== sidebar ===== -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="<?= $profileImg ?>" class="logo1" id="sidebarImg">
            <span class="logo-text1"> <?= htmlspecialchars(ucfirst(strtolower($adminName))) ?></span>

        </div>

        <div class="toggle-btn" id="toggleBtn">
            <i class='bx bx-chevron-left'></i>
        </div>

        <div class="menu">
            <ul>
                <li class="<?= $profileCompleted ? 'active' : 'locked' ?>" data-page="dashboard.php">
                    <i class="bx bxs-dashboard"></i>
                    <span class="text">Dashboard</span>
                </li>
                <li class="<?= $profileCompleted ? '' : 'locked' ?>" data-page="appointment.php">
                    <i class="bx bxs-calendar-check"></i>
                    <span class="text">Appointments</span>
                </li>
                <li class="<?= $profileCompleted ? '' : 'locked' ?>" data-page="users.php">
                    <i class="bx bxs-user-detail"></i>
                    <span class="text">Users</span>
                </li>
                <li class="<?= $profileCompleted ? '' : 'locked' ?>" data-page="services.php">
                    <i class="bx bxs-briefcase"></i>
                    <span class="text">Services</span>
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

    <!-- ===== CONTENT ===== -->
    <main class="content-area" id="content-area"></main>
</div>

<script>
let isProfileComplete = <?= $profileCompleted ?>;
const content = document.getElementById("content-area");

//sidebar Toggle
document.getElementById('toggleBtn').onclick = () => {
    document.querySelector('.sidebar').classList.toggle('collapsed');
};

//Page Loader with Script Execution
function loadPage(page) {
    fetch(page)
        .then(res => res.text())
        .then(html => {
            content.innerHTML = html;
            
            const scripts = content.querySelectorAll("script");
            scripts.forEach(oldScript => {
                const newScript = document.createElement("script");
                newScript.text = oldScript.text;
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            });
        });
}

//Navigation Click Handler
document.querySelectorAll(".menu li, .bottom .profile").forEach(item => {
    item.onclick = () => {
        if (item.classList.contains("locked") && isProfileComplete === 0) {
            showProfileMessage();
            return;
        }
       
        document.querySelector(".sidebar .active")?.classList.remove("active");
        item.classList.add("active");

        if (item.dataset.page) loadPage(item.dataset.page);
    };
});

function showProfileMessage() {
    content.innerHTML = `
        <div class="profile-warning" style="text-align:center; padding: 50px;">
            <h2>WELCOME <?= htmlspecialchars($adminName) ?></h2>
            <p>Please complete your profile to unlock all features.</p>
            <button class="save-btn" onclick="loadPage('profile.php')">Complete Profile Now</button>
        </div>`;
}

//Global Form Submission 
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'profileForm') {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('.save-btn');
        const formData = new FormData(form);
        formData.append('ajax_save', '1');

        btn.disabled = true;
        btn.innerText = "Saving...";

        fetch('profile.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("✅ Profile Updated Successfully!");
                    location.reload(); 
                } else {
                    alert("Error: " + data.message);
                    btn.disabled = false;
                    btn.innerText = "Save Changes";
                }
            })
            .catch(err => {
                
                if (!btn.disabled) alert("An error occurred.");
            });
    }
});


document.addEventListener("DOMContentLoaded", () => {
    isProfileComplete === 0 ? showProfileMessage() : loadPage("dashboard.php");
});
</script>

</body>
</html>

<?php if (isset($_GET['slot']) && $_GET['slot'] === 'added'): ?>
<script>
    alert("✅ SLOT ADDED SUCCESSFULLY!");
</script>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<script>
alert("<?= $_SESSION['flash_success'] ?>");
</script>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<script>
alert("<?= $_SESSION['flash_error'] ?>");
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

