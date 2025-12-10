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

// Get kategori name from KATEGORI table
$kategori_name = '';
$kategori_query = "SELECT nama_kategori FROM KATEGORI WHERE ID_kategori = ?";
$kategori_stmt = $conn->prepare($kategori_query);
$kategori_stmt->bind_param("i", $ID_kategori);
$kategori_stmt->execute();
$kategori_result = $kategori_stmt->get_result();
if ($kategori_row = $kategori_result->fetch_assoc()) {
    $kategori_name = $kategori_row['nama_kategori'];
}
$kategori_stmt->close();

// Insert into barang table (mapped field names)
$sql = "INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, harga_seunit, nama_pembekal, baki_semasa) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: admin_add_product.php?error=" . urlencode("Ralat pangkalan data: " . $conn->error));
    exit;
}

// Bind params: s=string, i=int, d=double
// no_kod, perihal_stok, ID_kategori, kategori, harga_seunit, nama_pembekal, baki_semasa
$stmt->bind_param("ssisssi", $id_produk, $nama_produk, $ID_kategori, $kategori_name, $harga, $nama_pembekal, $stok_semasa);

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
