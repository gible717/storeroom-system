<?php
// FILE: upload_profile_picture.php
// This script handles the upload for BOTH staff and admin,
// as they are all in the 'staf' table.

// 1. Start session and check authentication
session_start();
require 'db.php'; // Include your database connection

// We check if *any* user is logged in. 
// We will use the 'staff_auth_check.php' logic.
if (!isset($_SESSION['ID_staf']) || !isset($_SESSION['peranan'])) {
    header("Location: login.php?error=" . urlencode("Sila log masuk."));
    exit();
}

// 2. Get the user's ID from the SESSION (much safer than POST)
$id_staf = $_SESSION['ID_staf'];
$user_role = $_SESSION['peranan']; // 'Staf', 'Admin', or 'Superadmin'

// 3. Check if a file was uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    
    $file = $_FILES['profile_picture'];
    $upload_dir = 'uploads/profile_pictures/'; // Make sure this folder exists!
    
    // Create the directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 4. Validation
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    // Check if it's a real image
    if (getimagesize($file['tmp_name']) === false) {
        $error = "Fail bukan imej.";
    } 
    // Check file size
    elseif ($file['size'] > $max_size) {
        $error = "Saiz fail terlalu besar (Max 5MB).";
    } 
    // Check file type
    elseif (!in_array($imageFileType, $allowed_types)) {
        $error = "Hanya fail JPG, JPEG, PNG & GIF dibenarkan.";
    } else {
        // 5. Create a unique, permanent file name
        // We use the ID_staf to ensure it's unique and overwrites the old one
        $new_filename = $id_staf . '.' . $imageFileType;
        $target_path = $upload_dir . $new_filename;

        // 6. Delete the old picture (if it exists)
        // This prevents wasting space with old jpgs, pngs, etc.
        $stmt_old = $conn->prepare("SELECT gambar_profil FROM staf WHERE ID_staf = ?");
        $stmt_old->bind_param("s", $id_staf);
        $stmt_old->execute();
        $old_pic = $stmt_old->get_result()->fetch_assoc();
        
        if ($old_pic && !empty($old_pic['gambar_profil']) && file_exists($old_pic['gambar_profil'])) {
            if ($old_pic['gambar_profil'] != $target_path) {
                unlink($old_pic['gambar_profil']); // Delete old file
            }
        }
        $stmt_old->close();

        // 7. Move the new file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // 8. Update the database
            $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = ? WHERE ID_staf = ?");
            $stmt_update->bind_param("ss", $target_path, $id_staf);
            $stmt_update->execute();
            $stmt_update->close();
            
            $success = "Gambar profil berjaya dikemaskini.";
            
        } else {
            $error = "Ralat semasa memuat naik fail.";
        }
    }
} else {
    $error = "Tiada fail dipilih atau ralat muat naik.";
}

$conn->close();

// 9. Redirect back to the correct profile page
$redirect_page = 'staff_profile.php'; // Default
if ($user_role == 'Admin' || $user_role == 'Superadmin') {
    // We assume admins have a different profile page, 
    // If not, just use staff_profile.php
    // $redirect_page = 'admin_profile.php'; 
    $redirect_page = 'staff_profile.php'; // Keeping it simple for now
}

if (isset($error)) {
    header("Location: $redirect_page?error=" . urlencode($error));
} else {
    header("Location: $redirect_page?success=" . urlencode($success));
}
exit();
?>