<?php
// FILE: update_request_status.php (Safer & More Robust Version)
require 'admin_auth_check.php'; // Ensures only Admins can access

// 1. Get the Request ID and Action from the URL
$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

// 2. Validate the input
if (!$request_id || !in_array($action, ['approve', 'reject', 'complete'])) {
    header("Location: manage_requests.php?error=" . urlencode("Tindakan tidak sah."));
    exit;
}

// 3. Get the Admin's ID from the session to log who approved/rejected it.
$admin_id = $_SESSION['ID_staf'];
$new_status = ($action === 'approve') ? 'Diluluskan' : 'Ditolak';

// --- THE CRITICAL LOGIC FOR APPROVAL ---
if ($action === 'approve') {
    // Start a transaction. This is our all-or-nothing safety guarantee.
    $conn->begin_transaction();

    try {
        // Step A: Get the request details
        $stmt_get = $conn->prepare("SELECT ID_produk, jumlah_diminta FROM permohonan WHERE ID_permohonan = ? AND status = 'Belum Diproses' FOR UPDATE");
        $stmt_get->bind_param("i", $request_id);
        $stmt_get->execute();
        $request = $stmt_get->get_result()->fetch_assoc();

        if (!$request) {
            throw new Exception("Permohonan tidak dijumpai atau telah diproses oleh admin lain.");
        }

        $product_id = $request['ID_produk'];
        $quantity_requested = $request['jumlah_diminta'];

        // Step B: Check current stock
        $stmt_stock = $conn->prepare("SELECT stok_semasa, harga FROM produk WHERE ID_produk = ? FOR UPDATE");
        $stmt_stock->bind_param("s", $product_id);
        $stmt_stock->execute();
        $product = $stmt_stock->get_result()->fetch_assoc();

        if (!$product || $product['stok_semasa'] < $quantity_requested) {
            throw new Exception("Stok tidak mencukupi untuk meluluskan permohonan ini.");
        }

       // Step C: If stock is sufficient, deduct the quantity.
        $stmt_update_stock = $conn->prepare("UPDATE produk SET stok_semasa = stok_semasa - ? WHERE ID_produk = ?");
        $stmt_update_stock->bind_param("is", $quantity_requested, $product_id);
        $stmt_update_stock->execute();

        // --- FIX: Define variables for the status update ---
        $new_status = 'Diluluskan';
        $completed_date = date('Y-m-d H:i:s'); // Get the current time

        // Step D: Update the status of the request.
        $stmt_update_status = $conn->prepare("UPDATE permohonan SET status = ?, tarikh_selesai = ? WHERE ID_permohonan = ?");
        $stmt_update_status->bind_param("ssi", $new_status, $completed_date, $request_id);
        $stmt_update_status->execute();
        
        // --- STEP E: Corrected Transaction Log ---
        // Log the stock movement in the 'transaksi_inventori' table
        $jenis_transaksi = 'Keluar'; // Stock OUT
        $jumlah_transaksi = -$quantity_requested; // Store as a negative number (as per your original code)
        $tarikh_transaksi = date('Y-m-d H:i:s');
        
        // Get product price for logging
        $harga_seunit = $product['harga'] ?? 0.00; // 'harga' from produk table
        $jumlah_harga = $harga_seunit * $quantity_requested;

        // Use the request ID as the document number for reference
        $no_dokumen_ref = 'REQ' . str_pad($request_id, 4, '0', STR_PAD_LEFT); 

        // Corrected SQL for the 'transaksi_inventori' table structure
        $sql_transaksi = "INSERT INTO transaksi_inventori 
                        (ID_produk, ID_staf, jenis_transaksi, jumlah_transaksi, tarikh_transaksi, harga_seunit, jumlah_harga, no_dokumen)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; // Matches your screenshot

        $stmt_transaksi = $conn->prepare($sql_transaksi);
        if (!$stmt_transaksi) {
             // If prepare fails, rollback and throw error
            throw new Exception("Gagal menyediakan log transaksi: " . $conn->error);
        }

        // Corrected bind_param: s(ID_produk), s(ID_staf), s(jenis), i(jumlah), s(tarikh), d(harga_seunit), d(jumlah_harga), s(no_dokumen)
        $stmt_transaksi->bind_param("ssisidds", 
            $product_id, 
            $admin_id, 
            $jenis_transaksi, 
            $jumlah_transaksi, 
            $tarikh_transaksi, 
            $harga_seunit, 
            $jumlah_harga,
            $no_dokumen_ref // Pass the formatted request ID here
        );
        
        if (!$stmt_transaksi->execute()) {
             // If execute fails, rollback and throw error
            throw new Exception("Gagal melaksanakan log transaksi: " . $stmt_transaksi->error);
        }
        $stmt_transaksi->close(); // Close the statement

        // Commit the transaction
        $conn->commit();
        header("Location: manage_requests.php?success=" . urlencode("Permohonan telah diluluskan dan stok telah dikemaskini."));

    } catch (Exception $e) {
        // If anything fails, undo all changes
        $conn->rollback();
        header("Location: manage_requests.php?error=" . urlencode($e->getMessage()));
    }


} elseif ($action === 'complete') {
    // --- LOGIC FOR COMPLETION ---
    $new_status = 'Selesai';
    $completed_date = date('Y-m-d H:i:s'); // Get the current time for our trace record
    
    $stmt = $conn->prepare("UPDATE permohonan SET status = ?, tarikh_selesai = ? WHERE ID_permohonan = ? AND status = 'Diluluskan'");
    $stmt->bind_param("ssi", $new_status, $completed_date, $request_id);

    if ($stmt->execute()) {
        header("Location: manage_requests.php?success=" . urlencode("Permohonan telah ditandakan sebagai selesai."));
    } else {
        header("Location: manage_requests.php?error=" . urlencode("Gagal mengemaskini status permohonan."));
    }

} else {
    // --- SIMPLE LOGIC FOR REJECTION ---
    // For rejections, we only need to update the status. No transaction needed.
    $stmt = $conn->prepare("UPDATE permohonan SET status = ? WHERE ID_permohonan = ? AND status = 'Belum Diproses'");
    $stmt->bind_param("si", $new_status, $request_id);

    if ($stmt->execute()) {
        header("Location: manage_requests.php?success=" . urlencode("Permohonan telah ditolak."));
    } else {
        header("Location: manage_requests.php?error=" . urlencode("Gagal mengemaskini status permohonan."));
    }
}

$conn->close();
exit;
?>