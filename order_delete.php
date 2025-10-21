<?php
// FILE: order_delete.php
require 'admin_auth_check.php';

// 1. Get the ID from the URL
$id_pesanan = $_GET['id'] ?? null;
if (!$id_pesanan) {
    header("Location: admin_orders.php?error=" . urlencode("ID Pesanan tidak sah."));
    exit;
}

// 2. Prepare SQL DELETE statement
// We only need to delete from the main 'pesanan' table.
// The 'pesanan_item' table will be cleared automatically by the
// "ON DELETE CASCADE" foreign key we set up.
$sql = "DELETE FROM pesanan WHERE ID_pesanan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_pesanan); // 's' because ID_pesanan is VARCHAR

// 3. Execute and check for errors
if ($stmt->execute()) {
    // Success: redirect back to the order list
    header("Location: admin_orders.php?success=" . urlencode("Pesanan telah berjaya dipadam/dibatalkan."));
} else {
    // Fail: redirect back with a generic error
    header("Location: admin_orders.php?error=" . urlencode("Gagal memadam pesanan."));
}

// 4. Close resources
$stmt->close();
$conn->close();
exit;
?>