<?php
// auth_check.php - Core authentication check for all pages

// Harden session cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => isset($_SERVER['HTTPS']),
    ]);
    session_start();
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header("Location: index.php?error=Sila log masuk dahulu");
    exit;
}

// Session timeout - 30 minutes of inactivity
$session_timeout = 30 * 60; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $inactive_time = time() - $_SESSION['last_activity'];
    if ($inactive_time > $session_timeout) {
        // Session expired - destroy and redirect
        session_unset();
        session_destroy();
        header("Location: login.php?error=" . urlencode("Sesi anda telah tamat tempoh kerana tidak aktif. Sila log masuk semula."));
        exit;
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Load session variables
$userID = $_SESSION['ID_staf'];
$userName = $_SESSION['nama'];
$isAdmin = $_SESSION['is_admin'] ?? 0;
?>
