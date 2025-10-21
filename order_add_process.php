<?php
// FILE: order_add_process.php
require 'admin_auth_check.php'; // Ensures only Admins can access

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get Order Header Data
    $id_pembekal = $_POST['id_pembekal'];
    $tarikh_pesan = $_POST['tarikh_pesan'];
    $id_admin = $_SESSION['ID_staf']; // Get the admin's ID from the session

    // 2. Get Order Item Data (Arrays)
    $product_ids = $_POST['products']['id'] ?? [];
    $quantities = $_POST['products']['qty'] ?? [];

    // 3. Validation
    if (empty($id_pembekal) || empty($tarikh_pesan) || empty($product_ids)) {
        header("Location: order_add.php?error=" . urlencode("Sila isi semua maklumat (Pembekal, Tarikh, dan sekurang-kurangnya satu item)."));
        exit;
    }

    // --- Start Database Transaction ---
    // A transaction ensures that if one part fails, all parts are rolled back.
    // This prevents creating an "empty" order with no items.
    $conn->begin_transaction();

    try {
        // --- STEP A: Insert into the 'pesanan' (Order Header) table ---
        $sql_order = "INSERT INTO pesanan (ID_pembekal, tarikh_pesan, ID_admin) VALUES (?, ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("sss", $id_pembekal, $tarikh_pesan, $id_admin);
        $stmt_order->execute();

        // --- STEP B: Get the new 'ID_pesanan' that was just created ---
        $id_pesanan = $conn->insert_id;

        // --- STEP C: Insert into the 'pesanan_item' (Order Details) table ---
        $sql_item = "INSERT INTO pesanan_item (ID_pesanan, ID_produk, kuantiti_dipesan) VALUES (?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        // Loop through each product and insert it
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];

            // Basic validation for each item
            if (!empty($product_id) && !empty($quantity) && $quantity > 0) {
                $stmt_item->bind_param("isi", $id_pesanan, $product_id, $quantity);
                $stmt_item->execute();
            }
        }

        // --- STEP D: If all queries were successful, commit the transaction ---
        $conn->commit();
        
        // Redirect to the (future) order list page with a success message
        header("Location: admin_orders.php?success=" . urlencode("Pesanan baru berjaya dicipta."));

    } catch (Exception $e) {
        // --- STEP E: If any query failed, roll back all changes ---
        $conn->rollback();
        
        // Redirect back to the form with an error message
        header("Location: order_add.php?error=" . urlencode("Gagal mencipta pesanan: " . $e->getMessage()));
    }

    // 6. Close resources
    $stmt_order->close();
    $stmt_item->close();
    $conn->close();
    exit;

} else {
    // If someone tries to access this file directly, redirect them
    header("Location: admin_orders.php");
    exit;
}
?>