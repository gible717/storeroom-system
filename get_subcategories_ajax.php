<?php
// get_subcategories_ajax.php - Returns subcategories for a given parent category ID

session_start();
require_once 'db.php';
require_once 'admin_auth_check.php';

header('Content-Type: application/json');

if ($isAdmin != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

if ($parent_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID kategori tidak sah.']);
    exit;
}

$stmt = $conn->prepare("SELECT ID_kategori, nama_kategori FROM KATEGORI WHERE parent_id = ? ORDER BY nama_kategori ASC");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}
$stmt->close();

echo json_encode([
    'status' => 'success',
    'subcategories' => $subcategories,
    'has_subcategories' => count($subcategories) > 0
]);

$conn->close();
exit;
?>
