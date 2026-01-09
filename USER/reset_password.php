<?php
require "../config.php";

$token = $_GET['token'] ?? '';
$message = '';
$type = '';

if ($token === '') {
    die("❌ Invalid or missing reset token.");
}

/* ===== CHECK TOKEN + EXPIRY ===== */
$stmt = $conn->prepare("
    SELECT id 
    FROM users 
    WHERE reset_token = ? AND reset_expires > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("⏰ Reset link expired. Please request again.");
}

$user = $result->fetch_assoc();

/* ===== HANDLE FORM SUBMIT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || $confirm === '') {
        $message = "⚠️ All fields are required";
        $type = "error";
    }
    elseif ($new !== $confirm) {
        $message = "❌ Passwords do not match";
        $type = "error";
    }
    elseif (strlen($new) < 8) {
        $message = "⚠️ Minimum 8 characters required";
        $type = "error";
    }
    elseif (!preg_match('/[A-Z]/', $new)) {
        $message = "⚠️ Add at least 1 uppercase letter";
        $type = "error";
    }
    elseif (!preg_match('/[a-z]/', $new)) {
        $message = "⚠️ Add at least 1 lowercase letter";
        $type = "error";
    }
    elseif (!preg_match('/[0-9]/', $new)) {
        $message = "⚠️ Add at least 1 number";
        $type = "error";
    }
    elseif (!preg_match('/[\W_]/', $new)) {
        $message = "⚠️ Add at least 1 special character";
        $type = "error";
    }
    else {

        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $update = $conn->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ");
        $update->bind_param("si", $hashed, $user['id']);
        $update->execute();

        $message = "✅ Password reset successful! Please login ";
        $type = "success";

        echo "<script>
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 2000);
        </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.box {
    width:360px;
    background:#0a1117;
    padding:30px;
    border-radius:18px;
    border:1px solid #00eaff;
    box-shadow:0 0 25px rgba(0,234,255,0.2);
}

h2 {
    text-align:center;
    color:#00eaff;
    margin-bottom:20px;
}

input {
    width:93%;
    padding:12px;
    margin-bottom:14px;
    border-radius:10px;
    background:#000;
    border:1px solid #00eaff;
    color:#fff;
}

button {
    width:100%;
    padding:12px;
    border-radius:12px;
    border:none;
    cursor:pointer;
    background:linear-gradient(135deg,#00e5ff,#00ff88);
    font-weight:bold;
}

.toast {
    padding:12px;
    border-radius:10px;
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}

.toast.success {
    background:#0f5132;
    color:#d1e7dd;
    border:1px solid #00ff88;
}

.toast.error {
    background:#842029;
    color:#f8d7da;
    border:1px solid #ff7777;
}
</style>
</head>

<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if (!empty($message)): ?>
        <div class="toast <?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>
