<?php
// FILE: upload_profile_picture.php - DEBUGGING VERSION
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to browser
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_errors.log'); // Log to file

ob_start(); // Catch any accidental output

session_start();
header('Content-Type: application/json');

// Log function
function logDebug($message) {
    error_log("[UPLOAD DEBUG] " . $message);
}

logDebug("Script started");

// Include database
try {
    require_once 'db.php';
    logDebug("DB connected successfully");
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Check session
if (!isset($_SESSION['ID_staf'])) {
    logDebug("No session ID_staf");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Sila log masuk.']);
    exit();
}

$id_staf = $_SESSION['ID_staf'];
logDebug("User ID: " . $id_staf);

// Check file upload
if (!isset($_FILES['profile_picture'])) {
    logDebug("No file in POST");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Tiada fail diterima.']);
    exit();
}

if ($_FILES['profile_picture']['error'] != UPLOAD_ERR_OK) {
    $error_code = $_FILES['profile_picture']['error'];
    logDebug("Upload error code: " . $error_code);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Ralat upload (kod: ' . $error_code . ')']);
    exit();
}

$file = $_FILES['profile_picture'];
logDebug("File received: " . $file['name'] . " Size: " . $file['size']);

// Create upload directory
$upload_dir = 'uploads/profile_pictures/';
if (!is_dir($upload_dir)) {
    logDebug("Creating directory: " . $upload_dir);
    if (!mkdir($upload_dir, 0777, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Gagal membuat folder.']);
        exit();
    }
}

// Validate image
$image_info = @getimagesize($file['tmp_name']);
if ($image_info === false) {
    logDebug("Not a valid image");
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Fail bukan imej.']);
    exit();
}

// Determine file type
$file_type = $_POST['file_type'] ?? 'image/jpeg';
$ext = ($file_type === 'image/png') ? '.png' : '.jpeg';
$new_filename = $id_staf . $ext;
$target_path = $upload_dir . $new_filename;

logDebug("Target path: " . $target_path);

// Delete old picture
try {
    $stmt_old = $conn->prepare("SELECT gambar_profil FROM staf WHERE ID_staf = ?");
    $stmt_old->bind_param("s", $id_staf);
    $stmt_old->execute();
    $old_pic = $stmt_old->get_result()->fetch_assoc();
    $stmt_old->close();
    
    if ($old_pic && !empty($old_pic['gambar_profil']) && file_exists($old_pic['gambar_profil'])) {
        if ($old_pic['gambar_profil'] != $target_path) {
            unlink($old_pic['gambar_profil']);
            logDebug("Deleted old picture: " . $old_pic['gambar_profil']);
        }
    }
} catch (Exception $e) {
    logDebug("Error deleting old pic: " . $e->getMessage());
}

// Save image
$source_image = null;
$save_success = false;

try {
    if ($file_type === 'image/png') {
        $source_image = imagecreatefrompng($file['tmp_name']);
        if ($source_image) {
            imagealphablending($source_image, true);
            imagesavealpha($source_image, true);
            $save_success = imagepng($source_image, $target_path, 9);
        }
    } else {
        $source_image = imagecreatefromjpeg($file['tmp_name']);
        if ($source_image) {
            $save_success = imagejpeg($source_image, $target_path, 85);
        }
    }
    
    if ($source_image) {
        imagedestroy($source_image);
    }
    
    if (!$save_success) {
        logDebug("Failed to save image");
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan imej.']);
        exit();
    }
    
    logDebug("Image saved successfully");
} catch (Exception $e) {
    logDebug("Error saving image: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
    exit();
}

// Update database
try {
    $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = ? WHERE ID_staf = ?");
    $stmt_update->bind_param("ss", $target_path, $id_staf);
    $stmt_update->execute();
    $affected = $stmt_update->affected_rows;
    $stmt_update->close();
    
    logDebug("Database updated. Affected rows: " . $affected);
} catch (Exception $e) {
    logDebug("Database update error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit();
}

$conn->close();

ob_end_clean(); // Clear any accidental output
echo json_encode(['success' => true, 'path' => $target_path]);
logDebug("Success response sent");
exit();
?>