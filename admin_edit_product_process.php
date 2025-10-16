<?php
// FILE: admin_edit_product_process.php
session_start();
require 'db.php'; 
require 'auth_check.php';

// Turn on error reporting for our try...catch block
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Security Check: Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_products.php");
    exit;
}

// 2. Data Retrieval from the form
$id_produk = trim($_POST['id_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
$stok_semasa = !empty($_POST['stok_semasa']) ? (int)$_POST['stok_semasa'] : 0;

// Basic validation
if (empty($id_produk) || empty($nama_produk)) {
    header("Location: admin_products.php?error=" . urlencode("Data tidak lengkap. Sila cuba lagi."));
    exit;
}

// 3. The SQL UPDATE Command
$sql = "UPDATE PRODUK SET nama_produk = ?, kategori = ?, harga = ?, stok_semasa = ? WHERE ID_produk = ?";

try {
    $stmt = $conn->prepare($sql);
    // Bind parameters: s = string, d = double, i = integer
    $stmt->bind_param("ssdii", $nama_produk, $kategori, $harga, $stok_semasa, $id_produk);
    
    // 4. Execute and Redirect with Feedback
    $stmt->execute();
    
    // Check if any row was actually updated
    if ($stmt->affected_rows > 0) {
        $message = "Produk '" . htmlspecialchars($nama_produk) . "' berjaya dikemaskini!";
    } else {
        $message = "Tiada perubahan dikesan untuk produk '" . htmlspecialchars($nama_produk) . "'.";
    }
    
    header("Location: admin_products.php?success=" . urlencode($message));

} catch (mysqli_sql_exception $e) {
    // Catch any database errors
    $error_message = "Ralat semasa mengemaskini produk: " . $e->getMessage();
    header("Location: admin_edit_product.php?id=" . urlencode($id_produk) . "&error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>