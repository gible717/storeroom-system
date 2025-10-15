<?php
// FILE: auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
if (!isset($_SESSION['ID_staf'])) {
    header("Location: index.php?error=Sila log masuk dahulu");
    exit;
}
$userID = $_SESSION['ID_staf'];
$userName = $_SESSION['nama'];
$userRole = $_SESSION['peranan'];
?>