<?php
session_start();

/* HANDLE ACTION */
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'yes') {

        // Destroy session
        $_SESSION = [];
        session_destroy();

        // Redirect to register/login page
        header("Location: ../index.php");
        exit;

    } elseif ($_GET['action'] === 'no') {

        // Go back to dashboard
        header("Location: user.php");
        exit;
    }
}
?>

<h1 class="dash-title">LOGOUT</h1>

<div class="logout-wrapper">
    <div class="logout-card">
        <p class="logout-msg">ARE YOU SURE...!</p>

        <div class="btn-row">
            <a href="logout.php?action=yes" id="btn2">YES</a>
            <a href="./user.php" id="btn3">NO</a>
        </div>
    </div>
</div>
