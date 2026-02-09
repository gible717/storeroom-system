<?php
// reset_password_process.php - Process new password update

session_start();
require 'db.php';
require_once 'csrf.php';

// Check if user has completed step 1 verification
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header("Location: forgot_password.php?error=" . urlencode("Sesi tamat tempoh. Sila cuba lagi."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgot_password.php");
    exit;
}

// Validate CSRF token
csrf_check('forgot_password.php');

$id_staf = $_SESSION['reset_id_staf'];
$old_password_hash = $_SESSION['reset_old_password'];
$kata_laluan_baru = $_POST['kata_laluan_baru'] ?? '';
$sahkan_kata_laluan = $_POST['sahkan_kata_laluan'] ?? '';

// Validation
if (empty($kata_laluan_baru) || empty($sahkan_kata_laluan)) {
    header("Location: reset_password.php?error=" . urlencode("Sila isi semua medan."));
    exit;
}

if ($kata_laluan_baru !== $sahkan_kata_laluan) {
    header("Location: reset_password.php?error=" . urlencode("Kata laluan tidak sepadan. Sila semak semula."));
    exit;
}

if (strlen($kata_laluan_baru) < 6) {
    header("Location: reset_password.php?error=" . urlencode("Kata laluan mestilah sekurang-kurangnya 6 aksara."));
    exit;
}

if (strlen($kata_laluan_baru) > 10) {
    header("Location: reset_password.php?error=" . urlencode("Kata laluan tidak boleh melebihi 10 aksara."));
    exit;
}

// IMPORTANT: Check if new password is same as old password
$is_same_password = password_verify($kata_laluan_baru, $old_password_hash);

// Log the verification attempt for debugging
//error_log("Password reset verification - ID: $id_staf, Same as old: " . ($is_same_password ? 'YES' : 'NO') .
          //", New password length: " . strlen($kata_laluan_baru) .
          //", Hash: " . substr($old_password_hash, 0, 30) . "...");

if ($is_same_password) {
    header("Location: reset_password.php?error=" . urlencode("Kata laluan baru tidak boleh sama dengan kata laluan lama. Sila gunakan kata laluan yang berbeza."));
    exit;
}

// Hash new password
$kata_laluan_hash = password_hash($kata_laluan_baru, PASSWORD_DEFAULT);

// Update password in database
$stmt = $conn->prepare("UPDATE staf SET kata_laluan = ?, is_first_login = 0 WHERE ID_staf = ?");
$stmt->bind_param("ss", $kata_laluan_hash, $id_staf);

if ($stmt->execute()) {
    $stmt->close();

    // Clear reset session data
    unset($_SESSION['reset_id_staf']);
    unset($_SESSION['reset_nama']);
    unset($_SESSION['reset_old_password']);
    unset($_SESSION['reset_verified']);

    // Redirect to login with success message
    header("Location: login.php?success=" . urlencode("Kata laluan berjaya ditukar! Sila log masuk dengan kata laluan baru anda."));
    exit;
} else {
    $stmt->close();
    header("Location: reset_password.php?error=" . urlencode("Ralat berlaku. Sila cuba lagi."));
    exit;
}
?>
