<?php
// delete_profile_picture.php - Handle profile picture deletion

session_start();
require 'db.php';

// Check login
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php?error=" . urlencode("Sila log masuk."));
    exit();
}

$id_staf = $_SESSION['ID_staf'];
$is_admin = $_SESSION['is_admin'] ?? 0;
$error = '';
$success = '';

// Get current picture path
$stmt_old = $conn->prepare("SELECT gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_old->bind_param("s", $id_staf);
$stmt_old->execute();
$old_pic = $stmt_old->get_result()->fetch_assoc();
$stmt_old->close();

if ($old_pic && !empty($old_pic['gambar_profil'])) {
    $file_to_delete = $old_pic['gambar_profil'];

    // Delete file from server
    if (file_exists($file_to_delete)) {
        if (!@unlink($file_to_delete)) {
            error_log("[STOREROOM] Failed to delete profile picture: $file_to_delete");
        }
    }

    // Clear path in database
    $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = NULL WHERE ID_staf = ?");
    $stmt_update->bind_param("s", $id_staf);
    $stmt_update->execute();
    $stmt_update->close();

    $success = "Gambar profil anda telah berjaya dipadam.";
} else {
    $error = "Anda tidak mempunyai gambar profil untuk dipadam.";
}

$conn->close();

// Redirect to profile page
$redirect_page = ($is_admin == 1) ? 'admin_profile.php' : 'staff_profile.php';

if (!empty($error)) {
    header("Location: $redirect_page?error=" . urlencode($error));
} else {
    header("Location: $redirect_page?success=" . urlencode($success));
}
exit();
?>