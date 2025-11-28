<?php
// auth_check.php - Core authentication check for all pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header("Location: index.php?error=Sila log masuk dahulu");
    exit;
}

// Load session variables
$userID = $_SESSION['ID_staf'];
$userName = $_SESSION['nama'];
$isAdmin = $_SESSION['is_admin'] ?? 0;
$isSuperAdmin = $_SESSION['is_superadmin'] ?? 0;
?>
