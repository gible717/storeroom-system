<?php
// admin_edit_product_process.php - Handles product update (returns JSON for AJAX)

session_start();
require_once 'admin_auth_check.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Ralat tidak diketahui.'];

// Check admin access
if ($isAdmin != 1) {
    $response['message'] = 'Akses tidak dibenarkan.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_produk = trim($_POST['id_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $ID_kategori = (int)($_POST['ID_kategori'] ?? 0);
    $nama_pembekal = trim($_POST['nama_pembekal'] ?? '');  // Optional - for record keeping only
    $harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
    $stok_semasa = (int)($_POST['stok_semasa'] ?? 0);

    // Validate required fields
    if (empty($id_produk) || empty($nama_produk)) {
        $response['message'] = 'ID Produk and Nama Produk wajib diisi.';
        echo json_encode($response);
        exit;
    }
    if (empty($ID_kategori)) {
        $response['message'] = 'Kategori wajib dipilih.';
        echo json_encode($response);
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

    // Update barang table (mapped field names)
    $sql = "UPDATE barang SET perihal_stok = ?, ID_kategori = ?, kategori = ?, harga_seunit = ?, nama_pembekal = ?, baki_semasa = ? WHERE no_kod = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $response['message'] = 'Ralat pangkalan data: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    // Bind params: s=string, i=int, d=double
    // perihal_stok, ID_kategori, kategori, harga_seunit, nama_pembekal, baki_semasa, no_kod
    $stmt->bind_param("sisdsss", $nama_produk, $ID_kategori, $kategori_name, $harga, $nama_pembekal, $stok_semasa, $id_produk);

    try {
        $stmt->execute();
        $response['status'] = 'success';
        $response['message'] = 'Produk berjaya dikemaskini!';
        $response['redirectUrl'] = 'admin_products.php';
    } catch (mysqli_sql_exception $e) {
        $response['message'] = 'Ralat kemaskini: ' . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

} else {
    $response['message'] = 'Kaedah tidak sah.';
}

echo json_encode($response);
exit;
?>
