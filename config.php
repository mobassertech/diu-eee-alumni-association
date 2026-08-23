<?php
$server = "localhost";
$username = "nexttechltd_diueee_admin";
$password = "diueee_admin";
$database = "nexttechltd_diueee_alumni";

$conn = mysqli_connect($server, $username, $password, $database);

if (!$conn) {
    die("<script>alert('Connection failed: " . mysqli_connect_error() . "');</script>");
}

//echo "<script>alert('Connection successful.');</script>";
?>
