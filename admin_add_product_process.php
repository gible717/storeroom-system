<?php
// admin_add_product_process.php - Handles new product form submission

session_start();
require 'db.php';
require 'auth_check.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_add_product.php");
    exit;
}

// Get form data
$id_produk = trim($_POST['id_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? '');
$ID_kategori = (int)$_POST['ID_kategori'];
$nama_pembekal = trim($_POST['nama_pembekal'] ?? '');  // Optional - for record keeping only
$harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
$stok_semasa = !empty($_POST['stok_semasa']) ? (int)$_POST['stok_semasa'] : 0;

// Validate required fields
if (empty($id_produk) || empty($nama_produk)) {
    header("Location: admin_add_product.php?error=" . urlencode("ID Produk dan Nama Produk wajib diisi."));
    exit;
}

// Insert into database
$sql = "INSERT INTO PRODUK (ID_produk, nama_produk, ID_kategori, harga, nama_pembekal, stok_semasa) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: admin_add_product.php?error=" . urlencode("Ralat pangkalan data: " . $conn->error));
    exit;
}

// Bind params: s=string, i=int, d=double
$stmt->bind_param("ssidsi", $id_produk, $nama_produk, $ID_kategori, $harga, $nama_pembekal, $stok_semasa);

try {
    $stmt->execute();
    header("Location: admin_products.php?success=" . urlencode("Produk '$nama_produk' berjaya ditambah!"));
} catch (mysqli_sql_exception $e) {
    // Error 1062 = duplicate entry
    if ($e->getCode() === 1062) {
        $error_message = "Ralat: ID Produk '$id_produk' sudah wujud. Sila gunakan ID yang lain.";
    } else {
        $error_message = "Ralat semasa menyimpan produk: " . $e->getMessage();
    }
    header("Location: admin_add_product.php?error=" . urlencode($error_message));
}

$stmt->close();
$conn->close();
exit;
?>
