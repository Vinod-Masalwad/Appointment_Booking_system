<?php
require "../config.php";

$token = $_GET['token'] ?? '';
$message = '';
$type = '';

if ($token === '') {
    die("❌ Invalid or missing token.");
}

/* ===== VERIFY TOKEN + EXPIRY ===== */
$stmt = $conn->prepare("
    SELECT id 
    FROM admins 
    WHERE reset_token = ? AND reset_expires > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("⏰ Reset link expired. Please request again.");
}

$admin = $result->fetch_assoc();

/* ===== HANDLE FORM SUBMIT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($password === '' || $confirm === '') {
        $message = "⚠️ All fields are required";
        $type = "error";
    }
    elseif ($password !== $confirm) {
        $message = "❌ Passwords do not match";
        $type = "error";
    }
    elseif (strlen($password) < 8) {
        $message = "⚠️ Minimum 8 characters required";
        $type = "error";
    }
    elseif (!preg_match('/[A-Z]/', $password)) {
        $message = "⚠️ Add at least 1 uppercase letter";
        $type = "error";
    }
    elseif (!preg_match('/[a-z]/', $password)) {
        $message = "⚠️ Add at least 1 lowercase letter";
        $type = "error";
    }
    elseif (!preg_match('/[0-9]/', $password)) {
        $message = "⚠️ Add at least 1 number";
        $type = "error";
    }
    elseif (!preg_match('/[\W_]/', $password)) {
        $message = "⚠️ Add at least 1 special character";
        $type = "error";
    }
    else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("
            UPDATE admins 
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ");
        $update->bind_param("si", $hash, $admin['id']);
        $update->execute();

        $message = "✅ Password reset successful! Please login 🔐";
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
<title>Admin Reset Password</title>

<style>
body {
    background:#02070c;
    color:#fff;
    font-family:Arial, sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.reset-box {
    width:360px;
    background:#0a1117;
    padding:25px;
    border-radius:16px;
    border:1px solid #00eaff;
    box-shadow:0 0 30px rgba(0,234,255,0.25);
}

h3 {
    text-align:center;
    margin-bottom:20px;
    color:#00eaff;
}

input {
    width:93%;
    padding:12px;
    margin-bottom:14px;
    border-radius:10px;
    background:#000;
    border:1px solid rgba(0,234,255,0.4);
    color:#fff;
}

button {
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    background:linear-gradient(135deg,#00e5ff,#00ff88);
    font-weight:600;
    cursor:pointer;
}

.toast {
    padding:12px;
    border-radius:10px;
    text-align:center;
    margin-bottom:14px;
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
    border:1px solid #ff6b6b;
}
</style>
</head>

<body>

<div class="reset-box">
    <h3>Reset Password</h3>

    <?php if (!empty($message)): ?>
        <div class="toast <?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>
