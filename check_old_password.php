<?php
// check_old_password.php - AJAX endpoint to verify if new password matches old password

session_start();
header('Content-Type: application/json');

// Security: Only allow AJAX requests from same origin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user has active reset session
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Session invalid', 'matches' => false]);
    exit;
}

// Get the password to check
$password = $_POST['password'] ?? '';

// Return false for empty password (don't show error yet)
if (empty($password) || strlen($password) < 6) {
    echo json_encode(['matches' => false]);
    exit;
}

// Get old password hash from session
$old_password_hash = $_SESSION['reset_old_password'] ?? '';

// If no old password hash exists, something went wrong - but don't block user
if (empty($old_password_hash)) {
    echo json_encode(['error' => 'No old password found', 'matches' => false]);
    exit;
}

// Check if new password matches old password using PHP's password_verify
// This properly compares hashed passwords
$matches = password_verify($password, $old_password_hash);

// Log for debugging (remove in production)
//error_log("Password check - ID: " . ($_SESSION['reset_id_staf'] ?? 'unknown') .
         // ", Password tested: '" . $password . "'" .
         // ", Hash exists: " . (!empty($old_password_hash) ? 'yes' : 'no') .
         // ", Hash preview: " . substr($old_password_hash, 0, 30) . "..." .
         // ", Matches: " . ($matches ? 'YES ❌' : 'NO ✅'));

echo json_encode([
    'matches' => $matches
]);
?>
