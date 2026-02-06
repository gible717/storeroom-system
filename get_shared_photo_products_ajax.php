<?php
// get_shared_photo_products_ajax.php - Returns products that share the same photo as the given product

session_start();
require_once 'db.php';
require_once 'admin_auth_check.php';

header('Content-Type: application/json');

if ($isAdmin != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

$product_id = isset($_GET['product_id']) ? trim($_GET['product_id']) : '';

if (empty($product_id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Produk tidak sah.']);
    exit;
}

// Get the photo path for this product
$stmt = $conn->prepare("SELECT gambar_produk FROM barang WHERE no_kod = ?");
$stmt->bind_param("s", $product_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || empty($row['gambar_produk'])) {
    echo json_encode(['status' => 'success', 'shared_products' => [], 'has_shared' => false]);
    exit;
}

$photo_path = $row['gambar_produk'];

// Find other products using the same photo
$stmt = $conn->prepare("SELECT no_kod AS id, perihal_stok AS nama FROM barang WHERE gambar_produk = ? AND no_kod != ? ORDER BY perihal_stok ASC");
$stmt->bind_param("ss", $photo_path, $product_id);
$stmt->execute();
$result = $stmt->get_result();

$shared_products = [];
while ($p = $result->fetch_assoc()) {
    $shared_products[] = $p;
}
$stmt->close();

echo json_encode([
    'status' => 'success',
    'shared_products' => $shared_products,
    'has_shared' => count($shared_products) > 0
]);

$conn->close();
exit;
?>
