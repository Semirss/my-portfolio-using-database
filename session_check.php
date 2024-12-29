<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to the login page
    header("Location: adminlogin.php");
    exit;
}
if (!isset($_SESSION)) {
    # code...
}
?>
