<?php
// forgot_password_process.php - Handle password reset process

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgot_password.php");
    exit;
}

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
    $_SESSION['reset_old_password'] = $user['kata_laluan']; // To prevent reusing same password
    $_SESSION['reset_verified'] = true;

    // Redirect to step 2 (reset password form)
    header("Location: reset_password.php");
    exit;
}

// If unknown step, redirect back
header("Location: forgot_password.php");
exit;
?>
