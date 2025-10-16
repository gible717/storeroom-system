<?php
// FILE: admin_delete_product.php
session_start();
require 'db.php'; 
require 'auth_check.php';

// Turn on error reporting for our try...catch block
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Get the Product ID from the URL
$id_produk = $_GET['id'] ?? null;

if (empty($id_produk)) {
    header("Location: admin_products.php?error=" . urlencode("ID Produk tidak sah."));
    exit;
}

// 2. The SQL DELETE Command
$sql = "DELETE FROM PRODUK WHERE ID_produk = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_produk);
    
    // 3. Execute and Redirect with Feedback
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $message = "Produk berjaya dipadam!";
    } else {
        $message = "Produk tidak ditemui atau sudah dipadam.";
    }
    
    header("Location: admin_products.php?success=" . urlencode($message));

} catch (mysqli_sql_exception $e) {
    // Catch any database errors
    $error_message = "Ralat semasa memadam produk: " . $e->getMessage();
    header("Location: admin_products.php?error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>