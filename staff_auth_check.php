<?php
// FILE: staff_auth_check.php (FIXED)
require_once 'auth_check.php'; // Includes the core file

// This is the main Staff security check
if ($isAdmin == 1) {
    // If user IS an Admin, kick them to the admin dashboard
    header("Location: admin_dashboard.php");
    exit;
}
?>