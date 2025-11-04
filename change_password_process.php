<?php
// FILE: change_password_process.php (FIXED)

// --- "STEAK" (FIX): "4x4" (Safe) Auth Check ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// "Slay" (Check) if user is logged in at all
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php?error=Sila log masuk dahulu");
    exit;
}
// --- END OF "STEAK" (FIX) ---


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: change_password.php');
    exit;
}

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validation
if (empty($new_password) || strlen($new_password) < 8) {
    header('Location: change_password.php?error=' . urlencode('Kata laluan mestilah sekurang-kurangnya 8 aksara.'));
    exit;
}
if ($new_password !== $confirm_password) {
    header('Location: change_password.php?error=' . urlencode('Kata laluan tidak sepadan.'));
    exit;
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$user_id = $_SESSION['ID_staf'];

// Prepare the update statement
$stmt = $conn->prepare("UPDATE staf SET katalaluan = ?, is_first_login = 0 WHERE ID_staf = ?");
$stmt->bind_param('ss', $hashed_password, $user_id);

if ($stmt->execute()) {
    
    // --- "STEAK" (FIX): "Slay" (Update) the session "ghost" (variable) ---
    $_SESSION['is_first_login'] = 0;
    
    // --- "STEAK" (FIX): "Slay" (Check) the NEW variable ---
    $dashboard_url = ($_SESSION['is_admin'] == 1) ? 'admin_dashboard.php' : 'staff_dashboard.php';
    
    header("Location: $dashboard_url?success=" . urlencode('Kata laluan anda telah berjaya dikemaskini.'));
    exit;
} else {
    header('Location: change_password.php?error=' . urlencode('Gagal mengemaskini kata laluan. Sila cuba lagi.'));
    exit;
}
?>