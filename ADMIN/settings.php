<?php
session_start();
require "../config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}
?>

<h1 class="dash-title">SECURITY SETTINGS</h1>

<div class="settings-container">

    <div class="settings-card modern">
        <h3>Password & Security</h3>
        <p class="muted-text">
            Manage your password to keep your account secure.
        </p>

        <div class="settings-actions">
            <button class="settings-btn primary" onclick="openChangeModal()">
                🔒 Change Password
            </button>

            <button class="settings-btn secondary" onclick="openForgotModal()">
                ❓ Forgot Password
            </button>
        </div>
    </div>

</div>

<!-- ================= CHANGE PASSWORD MODAL ================= -->
<div class="modal1" id="changePasswordModal">
    <div class="modal-box">
        <h3>Change Password</h3>

        <form id="changePasswordForm">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

            <button type="submit" class="settings-btn primary full">
                Update Password
            </button>
        </form>

        <button class="modal-close" onclick="closeChangeModal()">×</button>
    </div>
</div>

<!-- ================= FORGOT PASSWORD MODAL ================= -->
<div class="modal1" id="forgotPasswordModal">
    <div class="modal-box">
        <h3>Forgot Password</h3>
        <p class="muted-text">
            Enter your registered email to reset your password.
        </p>

        <form id="forgotPasswordForm">
            <input type="email" name="email" placeholder="Registered Email" required>

            <button type="submit" class="settings-btn primary full">
                Send Reset Link
            </button>
        </form>

        <button class="modal-close" onclick="closeForgotModal()">×</button>
    </div>
</div>

<!-- ================= JS ================= -->
<script>
function openChangeModal() {
    document.getElementById("changePasswordModal").classList.add("show");
}
function closeChangeModal() {
    document.getElementById("changePasswordModal").classList.remove("show");
}

function openForgotModal() {
    document.getElementById("forgotPasswordModal").classList.add("show");
}
function closeForgotModal() {
    document.getElementById("forgotPasswordModal").classList.remove("show");
}

/* CHANGE PASSWORD */
document.getElementById("changePasswordForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("change_password.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(res => res.text())
    .then(data => {

        let message = "";

        switch (data) {
            case "success":
                message = "✅ Password updated successfully!";
                closeChangeModal();
                this.reset();
                break;

            case "wrong_current":
                message = "❌ Current password is incorrect.";
                break;

            case "mismatch":
                message = "❌ New passwords do not match.";
                break;

            case "weak":
                message = "⚠️ Password must be at least 6 characters.";
                break;

            case "same_password":
                message = "⚠️ New password must be different from old.";
                break;

            default:
                message = "⚠️ Something went wrong. Try again.";
        }

        alert(message);
    });
});

/* FORGOT PASSWORD */
document.getElementById("forgotPasswordForm").addEventListener("submit", function(e) {
    e.preventDefault();
    fetch("forgot_password.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        closeForgotModal();
        this.reset();
    });
});
</script>
