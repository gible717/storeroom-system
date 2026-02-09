<?php
// profile_change_password_process.php - Handle password change

session_start();
require_once 'db.php';
require_once 'csrf.php';

// Check login
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['ID_staf'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// Validate CSRF token
$redirect = ($is_admin == 1) ? 'admin_profile.php' : 'staff_profile.php';
csrf_check($redirect);

// Set redirect paths
$profile_page = ($is_admin == 1) ? 'admin_profile.php' : 'staff_profile.php';
$change_pass_page = 'profile_change_password.php';

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
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

    // Verify current password
    $stmt = $conn->prepare("SELECT kata_laluan FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($current_password, $user['kata_laluan'])) {
        header("Location: $change_pass_page?error=" . urlencode("Kata laluan semasa anda tidak betul."));
        $stmt->close();
        $conn->close();
        exit;
    }

    // IMPORTANT: Check if new password is same as current password
    if (password_verify($new_password, $user['kata_laluan'])) {
        header("Location: $change_pass_page?error=" . urlencode("Kata laluan baru tidak boleh sama dengan kata laluan semasa anda. Sila gunakan kata laluan yang berbeza."));
        $stmt->close();
        $conn->close();
        exit;
    }

    // Update password
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE staf SET kata_laluan = ?, is_first_login = 0 WHERE ID_staf = ?");
    $stmt->bind_param("ss", $new_password_hashed, $user_id);

    if ($stmt->execute()) {
        $msg = urlencode("Kata laluan anda telah berjaya ditukar.");
        header("Location: $profile_page?success=" . $msg);
    } else {
        $msg = urlencode("Gagal mengemaskini kata laluan. Sila cuba lagi.");
        header("Location: $change_pass_page?error=" . $msg);
    }

    $stmt->close();
    $conn->close();
    exit;

} else {
    header('Location: '. $profile_page);
    exit;
}
?>