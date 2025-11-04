<?php
// FILE: admin_auth_check.php (FIXED)
require_once 'auth_check.php'; // Includes the file we just fixed

// This is the main Admin security check
if ($isAdmin != 1) {
    // If user is NOT an Admin, kick them to the login page
    header("Location: login.php?error=Akses ditolak.");
    exit;
}

// This variable is now available to all admin pages
$is_superadmin = $isSuperAdmin; // Just to make the variable name consistent
?>