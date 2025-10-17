<?php
// FILE: admin_auth_check.php (This is ONLY for Admin pages)
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in AND has the 'Admin' role
if (!isset($_SESSION['ID_staf']) || $_SESSION['peranan'] !== 'Admin') {
    // If not, destroy the session and redirect to login
    session_destroy();
    header("Location: login.php?error=" . urlencode("Akses tidak dibenarkan. Sila log masuk sebagai Admin."));
    exit;
}

// Make user info available to all admin pages
$userRole = $_SESSION['peranan'];
$userName = $_SESSION['nama'];
?>