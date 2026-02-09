<?php
// upload_profile_picture.php - Handle profile picture upload

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_errors.log');

ob_start();

session_start();
header('Content-Type: application/json');

// Load config first so safeError() is available even if db.php fails
require_once __DIR__ . '/config.php';

// Include database
try {
    require_once 'db.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => safeError('Gagal menyambung ke pangkalan data.', $e->getMessage())]);
    exit();
}

// Check session
if (!isset($_SESSION['ID_staf'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Sila log masuk.']);
    exit();
}

// Validate CSRF token
require_once 'csrf.php';
if (!csrf_validate()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Sesi anda telah tamat. Sila muat semula halaman.']);
    exit();
}

$id_staf = $_SESSION['ID_staf'];

// Check file upload
if (!isset($_FILES['profile_picture'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Tiada fail diterima.']);
    exit();
}

if ($_FILES['profile_picture']['error'] != UPLOAD_ERR_OK) {
    $error_code = $_FILES['profile_picture']['error'];
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Ralat upload (kod: ' . $error_code . ')']);
    exit();
}

$file = $_FILES['profile_picture'];

// Create upload directory
$upload_dir = 'uploads/profile_pictures/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Gagal membuat folder.']);
        exit();
    }
}

// Validate image - server-side MIME check (prevents spoofing)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$real_mime = $finfo->file($file['tmp_name']);
$allowed_profile_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($real_mime, $allowed_profile_types)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Fail bukan imej yang sah (JPG, PNG, GIF sahaja).']);
    exit();
}

$image_info = @getimagesize($file['tmp_name']);
if ($image_info === false) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Fail bukan imej.']);
    exit();
}

// Determine file type and path
$file_type = $_POST['file_type'] ?? 'image/jpeg';
$ext = ($file_type === 'image/png') ? '.png' : '.jpeg';
$new_filename = $id_staf . $ext;
$target_path = $upload_dir . $new_filename;

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
        }
    }
} catch (Exception $e) {
    // Continue even if deletion fails
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
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan imej.']);
        exit();
    }
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => safeError('Gagal memproses imej.', $e->getMessage())]);
    exit();
}

// Update database
try {
    $stmt_update = $conn->prepare("UPDATE staf SET gambar_profil = ? WHERE ID_staf = ?");
    $stmt_update->bind_param("ss", $target_path, $id_staf);
    $stmt_update->execute();
    $stmt_update->close();
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => safeError('Ralat pangkalan data.', $e->getMessage())]);
    exit();
}

$conn->close();

ob_end_clean();
echo json_encode(['success' => true, 'path' => $target_path]);
exit();
?>
