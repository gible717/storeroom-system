<?php
// FILE: profile_change_password_process.php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

// Get user info from session
$user_id = $_SESSION['ID_staf'];
$user_role = $_SESSION['peranan'];

// Determine redirect paths
$profile_page = ($user_role == 'Admin') ? 'admin_profile.php' : 'staff_profile.php';
$change_pass_page = 'profile_change_password.php'; // <-- UPDATED FILENAME

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: $change_pass_page?error=" . urlencode("Sila isi semua ruangan."));
        exit;
    }
    
    if (strlen($new_password) < 8) {
        header("Location: $change_pass_page?error=" . urlencode("Kata laluan baru mestilah sekurang-kurangnya 8 aksara."));
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        header("Location: $change_pass_page?error=" . urlencode("Kata laluan baru dan pengesahan tidak sepadan."));
        exit;
    }

    // --- Database Check ---
    $stmt = $conn->prepare("SELECT kata_laluan FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($current_password, $user['kata_laluan'])) {
        // Invalid current password
        header("Location: $change_pass_page?error=" . urlencode("Kata laluan semasa anda tidak betul."));
        $stmt->close();
        $conn->close();
        exit;
    }
    
    // --- Success: Update Password ---
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    
    $update_stmt = $conn->prepare("UPDATE staf SET kata_laluan = ? WHERE ID_staf = ?");
    $update_stmt->bind_param("ss", $new_password_hashed, $user_id);
    
    if ($update_stmt->execute()) {
        header("Location: $profile_page?success=" . urlencode("Kata laluan anda telah berjaya dikemaskini."));
    } else {
        header("Location: $change_pass_page?error=" . urlencode("Gagal mengemaskini kata laluan. Sila cuba lagi."));
    }
    
    $stmt->close();
    $update_stmt->close();
    $conn->close();
    exit;

} else {
    // Not a POST request
    header('Location: '. $profile_page);
    exit;
}
?>