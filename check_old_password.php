<?php
// check_old_password.php - AJAX endpoint to verify if new password matches old password

session_start();

// Security: Only allow AJAX requests from same origin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Check if user has active reset session
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Session invalid']);
    exit;
}

// Get the password to check
$password = $_POST['password'] ?? '';

if (empty($password)) {
    echo json_encode(['matches' => false]);
    exit;
}

// Get old password hash from session
$old_password_hash = $_SESSION['reset_old_password'] ?? '';

if (empty($old_password_hash)) {
    echo json_encode(['error' => 'No old password found']);
    exit;
}

// Check if new password matches old password
$matches = password_verify($password, $old_password_hash);

echo json_encode(['matches' => $matches]);
?>
