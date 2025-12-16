<?php
// check_current_password.php - AJAX endpoint to verify if new password matches current password

session_start();

// Security: Only allow AJAX requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once 'db.php';

// Get the password to check
$password = $_POST['password'] ?? '';

if (empty($password)) {
    echo json_encode(['matches' => false]);
    exit;
}

// Get current password hash from database
$user_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT kata_laluan FROM staf WHERE ID_staf = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Check if new password matches current password
$matches = password_verify($password, $user['kata_laluan']);

echo json_encode(['matches' => $matches]);
?>
