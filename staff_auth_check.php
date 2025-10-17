<?php
// FILE: staff_auth_check.php (Corrected and Final)
require 'db.php'; // This is the missing line that provides the database connection.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for the correct session variable 'ID_staf'
if (!isset($_SESSION['ID_staf'])) {
    session_destroy();
    header("Location: login.php?error=" . urlencode("Sila log masuk untuk akses halaman ini."));
    exit;
}

// Check if the user has the correct role for this section
if ($_SESSION['peranan'] !== 'Staf') {
    if ($_SESSION['peranan'] === 'Admin') {
         header("Location: admin_dashboard.php");
    } else {
         header("Location: login.php");
    }
    exit;
}

// Make user info available to all staff pages
$userRole = $_SESSION['peranan'];
$userName = $_SESSION['nama'];
?>