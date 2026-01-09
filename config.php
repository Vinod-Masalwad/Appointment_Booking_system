<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "appointment_system";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}

define("MAIL_HOST", "smtp.gmail.com");
define("MAIL_USER", "appointment.booking.abs@gmail.com");
define("MAIL_PASS", "nxfn qbxw upvm vayh");
define("MAIL_PORT", 587);


?>
