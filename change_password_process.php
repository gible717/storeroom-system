<?php
// FILE: change_password_process.php
require 'auth_check.php';

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
    // Determine the correct dashboard URL
    $dashboard_url = ($_SESSION['peranan'] === 'Admin') ? 'admin_dashboard.php' : 'staff_dashboard.php';
    
    // UPDATED: Redirect to the dashboard with a success message
    header("Location: $dashboard_url?success=" . urlencode('Kata laluan anda telah berjaya dikemaskini.'));
    exit;
} else {
    header('Location: change_password.php?error=' . urlencode('Gagal mengemaskini kata laluan. Sila cuba lagi.'));
    exit;
}
?>