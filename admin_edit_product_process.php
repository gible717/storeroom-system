<?php
// FILE: admin_edit_product_process.php (NOW 100% "SLAYED" FOR AJAX)
session_start();
// "SLAY" (FIX) 1: "Slay" (kill) the "Vibe" (GUI) header.
// We only require the "bland food" (logic) it needs.
require_once 'db.php';
require_once 'admin_auth_check.php';

// "SLAY" (FIX) 2: Prepare a "vibe" (JSON) response for your "Slay" (AJAX).
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Ralat tidak diketahui.'];

// "4x4" (Safe) Check: Ensure user is Admin
if ($userRole !== 'Admin') {
    $response['message'] = 'Akses tidak dibenarkan.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. "Slay" (Fix) Data Retrieval ---
    $id_produk = trim($_POST['id_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $ID_kategori = (int)($_POST['ID_kategori'] ?? 0); // "Slay" (Fix) 3: Get the new "smart" (logic) ID
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

    // --- 2. "Slay" (Fix) Database Update ---
    // We "slay" (kill) the "ghost" (kategori) and use the "steak" (ID_kategori)
    $sql = "UPDATE PRODUK SET 
                nama_produk = ?, 
                ID_kategori = ?, 
                harga = ?, 
                stok_semasa = ? 
            WHERE ID_produk = ?";
            
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $response['message'] = 'Ralat pangkalan data: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    // --- 3. "Slay" (Fix) Parameter Binding ---
    // The "vibe" (types) is now "sidis" (string, int, double, int, string)
    $stmt->bind_param("sidis", 
        $nama_produk,
        $ID_kategori,
        $harga,
        $stok_semasa,
        $id_produk
    );

    // --- 4. "Slay" (Execute) ---
    try {
        $stmt->execute();
        
        $response['status'] = 'success';
        $response['message'] = 'Produk berjaya dikemaskini!';
        
        // "SLAY" (FIX) 4: "Slay" (kill) the "Joker" (double pop-up) bug.
        // We "slay" (redirect) *silently*. The "vibe" (pop-up) is already done.
        $response['redirectUrl'] = 'admin_products.php';

    } catch (mysqli_sql_exception $e) {
        $response['message'] = 'Ralat kemaskini: ' . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

} else {
    // "Ghost" (Bug) check: If someone just types the URL
    $response['message'] = 'Kaedah tidak sah.';
}

// "SLAY" (FIX) 6: "Vibe" (send) the final "bland food" (JSON) to your "Slay" (AJAX).
echo json_encode($response);
exit;
?>