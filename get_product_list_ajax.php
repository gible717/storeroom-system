<?php
// get_product_list_ajax.php - Returns product list for "apply photo to others" dialog

session_start();
require_once 'db.php';
require_once 'admin_auth_check.php';

header('Content-Type: application/json');

if ($isAdmin != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

// Optional: exclude a specific product (the one being edited/added)
$exclude = trim($_GET['exclude'] ?? '');

$sql = "SELECT no_kod AS id, perihal_stok AS nama, gambar_produk FROM barang ORDER BY perihal_stok ASC";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    // Skip the excluded product
    if ($exclude !== '' && $row['id'] === $exclude) continue;

    $products[] = [
        'id' => $row['id'],
        'nama' => $row['nama'],
        'has_photo' => !empty($row['gambar_produk']) && file_exists($row['gambar_produk'])
    ];
}

echo json_encode(['status' => 'success', 'products' => $products]);
$conn->close();
exit;
?>
