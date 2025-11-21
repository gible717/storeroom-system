<?php
// FILE: admin_edit_product_process.php (NOW 100% "SLAYED" FOR AJAX)
session_start();
// "SLAY" (FIX): We ONLY need admin_auth_check.php.
// It "slays" (includes) db.php for us. This "slays" (kills) "ghosts" (bugs) 👻.
require_once 'admin_auth_check.php';

// "SLAY" (FIX): Prepare a "vibe" (JSON) response
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Ralat tidak diketahui.'];

// --- THIS IS THE "STEAK" (FIX) ---
// We "slay" (check) the "steak" (correct) variable: $isAdmin
if ($isAdmin != 1) {
    $response['message'] = 'Akses tidak dibenarkan.';
    echo json_encode($response);
    exit;
}
// --- END OF "STEAK" (FIX) ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. "Slay" (Get) Data ---
    $id_produk = trim($_POST['id_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $ID_kategori = (int)($_POST['ID_kategori'] ?? 0);
    $nama_pembekal = trim($_POST['nama_pembekal'] ?? '');
    $harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
    $stok_semasa = (int)($_POST['stok_semasa'] ?? 0);

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

    // --- 2. "Slay" (Prepare) Database Update ---
    $sql = "UPDATE PRODUK SET
                nama_produk = ?,
                ID_kategori = ?,
                harga = ?,
                nama_pembekal = ?,
                stok_semasa = ?
            WHERE ID_produk = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $response['message'] = 'Ralat pangkalan data: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    // --- 3. "Slay" (Bind) Parameters ---
    // The "vibe" (types) is "sidsis" (string, int, double, string, int, string)
    $stmt->bind_param("sidsis",
        $nama_produk,
        $ID_kategori,
        $harga,
        $nama_pembekal,
        $stok_semasa,
        $id_produk
    );

    // --- 4. "Slay" (Execute) ---
    try {
        $stmt->execute();
        
        $response['status'] = 'success';
        $response['message'] = 'Produk berjaya dikemaskini!';
        $response['redirectUrl'] = 'admin_products.php'; // "Slay" (Tell) AJAX where to go

    } catch (mysqli_sql_exception $e) {
        $response['message'] = 'Ralat kemaskini: ' . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

} else {
    $response['message'] = 'Kaedah tidak sah.';
}

// "SLAY" (FIX): "Vibe" (send) the final "steak" (JSON)
echo json_encode($response);
exit;
?>