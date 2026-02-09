<?php
// change_password_process.php - Handle first-time password change

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'csrf.php';

// Check login
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php?error=Sila log masuk dahulu");
    exit;
}

// Validate CSRF token
csrf_check('change_password.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: change_password.php');
    exit;
}

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validate password (6-10 characters)
if (empty($new_password) || strlen($new_password) < 6) {
    header('Location: change_password.php?error=' . urlencode('Kata laluan mestilah sekurang-kurangnya 6 aksara.'));
    exit;
}
if (strlen($new_password) > 10) {
    header('Location: change_password.php?error=' . urlencode('Kata laluan tidak boleh melebihi 10 aksara.'));
    exit;
}
if ($new_password !== $confirm_password) {
    header('Location: change_password.php?error=' . urlencode('Kata laluan tidak sepadan.'));
    exit;
}

// Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$user_id = $_SESSION['ID_staf'];

$stmt = $conn->prepare("UPDATE staf SET kata_laluan = ?, is_first_login = 0 WHERE ID_staf = ?");
$stmt->bind_param('ss', $hashed_password, $user_id);

if ($stmt->execute()) {
    $_SESSION['is_first_login'] = 0;
    $dashboard_url = ($_SESSION['is_admin'] == 1) ? 'admin_dashboard.php' : 'staff_dashboard.php';
    header("Location: $dashboard_url?success=" . urlencode('Kata laluan anda telah berjaya dikemaskini.'));
    exit;
} else {
    header('Location: change_password.php?error=' . urlencode('Gagal mengemaskini kata laluan. Sila cuba lagi.'));
    exit;
}
?>