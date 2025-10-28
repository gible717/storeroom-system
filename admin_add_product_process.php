<?php
// FILE: admin_add_product_process.php (Final Version with Exception Handling)
session_start();
require 'db.php'; 
require 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_add_product.php");
    exit;
}

// --- 1. Data Retrieval from your form ---
$id_produk = trim($_POST['id_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? '');
$ID_kategori = (int)$_POST['ID_kategori'];
$harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
$stok_semasa = !empty($_POST['stok_semasa']) ? (int)$_POST['stok_semasa'] : 0;

if (empty($id_produk) || empty($nama_produk)) {
    header("Location: admin_add_product.php?error=" . urlencode("ID Produk dan Nama Produk wajib diisi."));
    exit;
}

// --- 2. Database Insertion with Correct Table/Column Names ---
$sql = "INSERT INTO PRODUK (ID_produk, nama_produk, ID_kategori, harga, stok_semasa) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: admin_add_product.php?error=" . urlencode("Ralat pangkalan data: " . $conn->error));
    exit;
}

$stmt->bind_param("ssidi", $id_produk, $nama_produk, $ID_kategori, $harga, $stok_semasa);

// --- 3. Execute and Redirect using try...catch ---
try {
    // Attempt to execute the statement
    $stmt->execute();
    
    // If it succeeds, redirect with a success message
    header("Location: admin_products.php?success=" . urlencode("Produk '$nama_produk' berjaya ditambah!"));

} catch (mysqli_sql_exception $e) {
    // If it fails, "catch" the exception
    
    // Check if the error code is 1062 (duplicate entry)
    if ($e->getCode() === 1062) {
        $error_message = "Ralat: ID Produk '$id_produk' sudah wujud. Sila gunakan ID yang lain.";
    } else {
        // For any other database error
        $error_message = "Ralat semasa menyimpan produk: " . $e->getMessage();
    }
    
    // Redirect back to the form with the user-friendly error message
    header("Location: admin_add_product.php?error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>