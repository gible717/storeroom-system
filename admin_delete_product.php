<?php
// admin_delete_product.php - Handle product deletion

session_start();
require 'db.php';
require 'auth_check.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Get product ID from URL
$id_produk = $_GET['id'] ?? null;

if (empty($id_produk)) {
    header("Location: admin_products.php?error=" . urlencode("ID Produk tidak sah."));
    exit;
}

// Delete product
$sql = "DELETE FROM PRODUK WHERE ID_produk = ?";

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
    $error_message = "Ralat semasa memadam produk: " . $e->getMessage();
    header("Location: admin_products.php?error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>
