<?php
// admin_auth_check.php - Admin-only access check

require_once 'auth_check.php';

// Block non-admin users
if ($isAdmin != 1) {
    header("Location: login.php?error=Akses ditolak.");
    exit;
}
?>
