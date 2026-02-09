<?php
// admin_delete_product.php - Handle product deletion

session_start();
require 'db.php';
require 'auth_check.php';
require_once 'csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Validate CSRF token (passed as URL parameter)
$csrf_token = $_GET['token'] ?? '';
if (!csrf_validate($csrf_token)) {
    header("Location: admin_products.php?error=" . urlencode("Sesi anda telah tamat. Sila cuba lagi."));
    exit;
}

// Get product ID from URL
$id_produk = $_GET['id'] ?? null;

if (empty($id_produk)) {
    header("Location: admin_products.php?error=" . urlencode("ID Produk tidak sah."));
    exit;
}

// Delete product from barang table
$sql = "DELETE FROM barang WHERE no_kod = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_produk);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Produk berjaya dipadam!";
    } else {
        $message = "Produk tidak ditemui atau sudah dipadam.";
    }

    header("Location: admin_products.php?success=" . urlencode($message));

} catch (mysqli_sql_exception $e) {
    $error_message = safeError("Ralat semasa memadam produk.", $e->getMessage());
    header("Location: admin_products.php?error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>
