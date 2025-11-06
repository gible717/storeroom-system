<?php
// FILE: upload_profile_picture.php
// This is the NEW backend (Solution B) designed to work with Cropper.js

// 1. Start session and set header to speak JSON
session_start();
require 'db.php';
header('Content-Type: application/json');

// 2. Authenticate the user
if (!isset($_SESSION['ID_staf'])) {
    // Send a JSON error, not an HTML redirect
    echo json_encode(['success' => false, 'error' => 'Sila log masuk.']);
    exit();
}

$id_staf = $_SESSION['ID_staf'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// 3. Check if the file from Cropper.js exists
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    
    $file = $_FILES['profile_picture'];
    $upload_dir = 'uploads/profile_pictures/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 4. Validate that it's a real image
    if (getimagesize($file['tmp_name']) === false) {
        echo json_encode(['success' => false, 'error' => 'Fail bukan imej.']);
        exit;
    } 

    // 5. Get the file type we sent from JavaScript
    $file_type = $_POST['file_type'] ?? 'image/jpeg'; // Get the type from the FETCH request
    $ext = '.jpeg'; // Default to .jpeg
    
    if ($file_type === 'image/png') {
        $ext = '.png';
    }

    // 6. Create the new, unique filename
    $new_filename = $id_staf . $ext;
    $target_path = $upload_dir . $new_filename;

    // 7. Delete the old picture (if one exists)
    $stmt_old = $conn->prepare("SELECT gambar_profil FROM staf WHERE ID_staf = ?");
    $stmt_old->bind_param("s", $id_staf);
    $stmt_old->execute();
    $old_pic = $stmt_old->get_result()->fetch_assoc();
    
    if ($old_pic && !empty($old_pic['gambar_profil']) && file_exists($old_pic['gambar_profil'])) {
        // Delete old file, *even if the extension is different*
        if ($old_pic['gambar_profil'] != $target_path) {
            unlink($old_pic['gambar_profil']); 
        }
    }
    $stmt_old->close();

    // 8. Save the uploaded (already cropped) file
    // We DON'T need resizeAndSquareImage() because Cropper.js already did the work!
    $source_image = null;
    if ($file_type === 'image/png') {
        // Create from PNG
        $source_image = imagecreatefrompng($file['tmp_name']);
        imagealphablending($source_image, true);
        imagesavealpha($source_image, true);
        imagepng($source_image, $target_path, 9); // Save as high-quality PNG
    } else {
        // Default to create from JPEG
        $source_image = imagecreatefromjpeg($file['tmp_name']);
        imagejpeg($source_image, $target_path, 85); // Save as 85% quality JPEG
    }
    
    if ($source_image) {
        imagedestroy($source_image);
    }

    // 9. Update the database
    $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = ? WHERE ID_staf = ?");
    $stmt_update->bind_param("ss", $target_path, $id_staf);
    $stmt_update->execute();
    $stmt_update->close();
    
    // 10. Send the "Success" JSON message back to the browser
    echo json_encode(['success' => true]);
    
} else {
    // Send a JSON error
    echo json_encode(['success' => false, 'error' => 'Tiada fail dipilih atau ralat muat naik.']);
}

$conn->close();
exit();
?>