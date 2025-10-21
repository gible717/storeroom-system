<?php
// FILE: order_receive_process.php
require 'admin_auth_check.php'; // Ensures only Admins can access

// 1. Get the Order ID from the URL
$id_pesanan = $_GET['id'] ?? null;
$admin_id = $_SESSION['ID_staf'];

if (!$id_pesanan) {
    header("Location: admin_orders.php?error=" . urlencode("ID Pesanan tidak sah."));
    exit;
}

// --- Start Database Transaction ---
// This ensures all stock updates and logs happen, or none of them do.
$conn->begin_transaction();

try {
    // 2. Get all items from the order
    $sql_items = "SELECT ID_produk, kuantiti_dipesan FROM pesanan_item WHERE ID_pesanan = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("s", $id_pesanan); // 's' because ID_pesanan is VARCHAR
    $stmt_items->execute();
    $items = $stmt_items->get_result();

    if ($items->num_rows == 0) {
        throw new Exception("Tiada item ditemui untuk pesanan ini.");
    }

    // Prepare statements for the loop
    $sql_update_stock = "UPDATE produk SET stok_semasa = stok_semasa + ? WHERE ID_produk = ?";
    $stmt_update_stock = $conn->prepare($sql_update_stock);

    $sql_transaksi = "INSERT INTO transaksi_inventori 
                        (ID_produk, ID_staf, ID_pesanan, jenis_transaksi, kuantiti_berubah, tarikh_transaksi)
                      VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt_transaksi = $conn->prepare($sql_transaksi);
    $jenis_transaksi = 'Masuk'; // Stock IN

    // 3. Loop through each item, update stock, and create transaction log
    while ($item = $items->fetch_assoc()) {
        $product_id = $item['ID_produk'];
        $quantity = $item['kuantiti_dipesan'];

        // Step A: Update the 'produk' table
        $stmt_update_stock->bind_param("is", $quantity, $product_id);
        $stmt_update_stock->execute();

        // Step B: Create the audit log in 'transaksi_inventori'
        $stmt_transaksi->bind_param("ssisi", $product_id, $admin_id, $id_pesanan, $jenis_transaksi, $quantity);
        $stmt_transaksi->execute();
    }

    // 4. Update the main order status to 'Selesai'
    $sql_update_order = "UPDATE pesanan SET status_pesanan = 'Selesai' WHERE ID_pesanan = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    $stmt_update_order->bind_param("s", $id_pesanan);
    $stmt_update_order->execute();

    // 5. If everything is successful, commit the changes
    $conn->commit();
    
    header("Location: admin_orders.php?success=" . urlencode("Stok berjaya dikemaskini. Pesanan ditandakan sebagai Selesai."));

} catch (Exception $e) {
    // 6. If anything fails, roll back all changes
    $conn->rollback();
    header("Location: admin_orders.php?error=" . urlencode("Gagal kemaskini stok: " . $e->getMessage()));
}

// 7. Close all resources
$stmt_items->close();
$stmt_update_stock->close();
$stmt_transaksi->close();
$stmt_update_order->close();
$conn->close();
exit;
?>