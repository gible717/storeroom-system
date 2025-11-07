<?php
// FILE: delete_profile_picture.php

// 1. Start session and check authentication
session_start();
require 'db.php'; // Include your database connection

// We check if *any* user is logged in.
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php?error=" . urlencode("Sila log masuk."));
    exit();
}

$id_staf = $_SESSION['ID_staf'];
$is_admin = $_SESSION['is_admin'] ?? 0;
$error = '';
$success = '';

// 2. Find the user's current picture from the database
$stmt_old = $conn->prepare("SELECT gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_old->bind_param("s", $id_staf);
$stmt_old->execute();
$old_pic = $stmt_old->get_result()->fetch_assoc();
$stmt_old->close();

// Check if a picture path exists in the database
if ($old_pic && !empty($old_pic['gambar_profil'])) {
    
    $file_to_delete = $old_pic['gambar_profil'];
    
    // 3. Delete the file from the server
    // We check if the file actually exists before trying to delete it
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete); // Delete the actual file (e.g., S001.jpeg)
    }
    
    // 4. Update the database, setting the path to NULL
    $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = NULL WHERE ID_staf = ?");
    $stmt_update->bind_param("s", $id_staf);
    $stmt_update->execute();
    $stmt_update->close();
    
    $success = "Gambar profil anda telah berjaya dipadam.";
    
} else {
    // This runs if the user tried to delete a picture that was already gone
    $error = "Anda tidak mempunyai gambar profil untuk dipadam.";
}

$conn->close();

// 5. Redirect back to the correct profile page
$redirect_page = 'staff_profile.php'; // Default to Staff
if ($is_admin == 1) {
    $redirect_page = 'admin_profile.php'; // Admins go to Admin Profile
}

// Send the user back with the appropriate message
if (!empty($error)) {
    header("Location: $redirect_page?error=" . urlencode($error));
} else {
    header("Location: $redirect_page?success=" . urlencode($success));
}
exit();
?>