<?php
// forgot_password_process.php - Handle password reset process

session_start();
require 'db.php';
require_once 'csrf.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgot_password.php");
    exit;
}

// Validate CSRF token
csrf_check('forgot_password.php');

$step = $_POST['step'] ?? '1';

// ============================================
// STEP 1: Verify Identity (ID_staf only)
// ============================================
if ($step == '1') {
    $id_staf = trim($_POST['ID_staf'] ?? '');

    if (empty($id_staf)) {
        header("Location: forgot_password.php?error=" . urlencode("Sila masukkan ID Staf anda."));
        exit;
    }

    // Clear any previous reset session data to prevent conflicts
    unset($_SESSION['reset_id_staf']);
    unset($_SESSION['reset_nama']);
    unset($_SESSION['reset_old_password']);
    unset($_SESSION['reset_verified']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT ID_staf, nama, kata_laluan FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $id_staf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User not found
        $stmt->close();
        header("Location: forgot_password.php?error=" . urlencode("ID Staf tidak dijumpai. Sila semak semula."));
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Store verification data in session (temporary)
    $_SESSION['reset_id_staf'] = $user['ID_staf'];
    $_SESSION['reset_nama'] = $user['nama'];
    $_SESSION['reset_old_password'] = $user['kata_laluan']; // Current hashed password to prevent reusing
    $_SESSION['reset_verified'] = true;
    $_SESSION['reset_timestamp'] = time(); // Track when reset was initiated

    // Log the reset attempt for debugging
    //error_log("Password reset initiated - ID: " . $user['ID_staf'] . ", Hash: " . substr($user['kata_laluan'], 0, 20) . "...");

    // Redirect to step 2 (reset password form)
    header("Location: reset_password.php");
    exit;
}

// If unknown step, redirect back
header("Location: forgot_password.php");
exit;
?>
