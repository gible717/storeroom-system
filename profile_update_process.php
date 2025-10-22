<?php
session_start(); // 1. Start the session to get the logged-in user

// 2. Check if a user is logged in
if (!isset($_SESSION['ID_staf'])) {
    // If no user is logged in, stop right here
    die("Sila log masuk untuk mengemaskini profil anda.");
}

// 3. Use __DIR__ to build an absolute path (This is the fix)
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from session
$user_id = $_SESSION['ID_staf'];
$user_role = $_SESSION['peranan'];

// Get data from form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];

    // Validation
    if (empty($nama) || empty($emel) || !filter_var($emel, FILTER_VALIDATE_EMAIL)) {
        $error_msg = urlencode("Nama dan emel yang sah diperlukan.");
        // Redirect back to the correct profile page based on role
        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
        exit;
    }

    // Prepare and execute the update
    $stmt = $conn->prepare("UPDATE staf SET nama = ?, emel = ? WHERE ID_staf = ?");
    $stmt->bind_param("sss", $nama, $emel, $user_id);
    
    if ($stmt->execute()) {
        $success_msg = urlencode("Profil anda telah berjaya dikemaskini.");
        // Update session name
        $_SESSION['nama'] = $nama;
        
        // Redirect back
        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?success=" . $success_msg);
        } else {
            header("Location: staff_profile.php?success=" . $success_msg);
        }
    } else {
        $error_msg = urlencode("Gagal mengemaskini profil.");
        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
    }
    
    $stmt->close();
    $conn->close();
    exit;

} else {
    // Not a POST request
    header('Location: ' . ($user_role == 'Admin' ? 'admin_dashboard.php' : 'staff_dashboard.php'));
    exit;
}
?>