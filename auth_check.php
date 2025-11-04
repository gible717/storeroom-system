<?php
// FILE: auth_check.php (FIXED)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// 1. Check if user is logged in at all
if (!isset($_SESSION['ID_staf'])) {
    header("Location: index.php?error=Sila log masuk dahulu");
    exit;
}

// 2. Load all session variables into local variables
$userID = $_SESSION['ID_staf'];
$userName = $_SESSION['nama'];
$isAdmin = $_SESSION['is_admin'] ?? 0;
$isSuperAdmin = $_SESSION['is_superadmin'] ?? 0;
?>