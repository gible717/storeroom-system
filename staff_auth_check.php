<?php
// staff_auth_check.php - Staff-only access check

require_once 'auth_check.php';

// Redirect admins to admin dashboard
if ($isAdmin == 1) {
    header("Location: admin_dashboard.php");
    exit;
}
?>
