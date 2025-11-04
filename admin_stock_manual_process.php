<?php
// FILE: admin_stock_manual_process.php (THE FINAL "SLAY" 🥹)

require_once 'admin_auth_check.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // "Vibe" (Get) the "steak" (correct) data
    $id_produk = $conn->real_escape_string($_POST['ID_produk']); 
    $jumlah_masuk = (int)$_POST['jumlah_masuk']; 
    $no_dokumen = $conn->real_escape_string($_POST['no_dokumen']);
    $id_staf = $userID; 
    $jenis_transaksi = 'Stok_Masuk';
    $tarikh_transaksi = date('Y-m-d H:i:s'); 

    // "Slay" (Set) the "bland food" (new) columns to null for "Stok_Masuk"
    $harga_seunit = null;
    $jumlah_harga = null;

    // "4x4" (Safe) Validation
    if ($jumlah_masuk <= 0 || empty($id_produk)) {
        header("Location: admin_stock_manual.php?error=Data tidak lengkap. Sila isi semua medan.");
        exit; 
    }

    $conn->begin_transaction();

    try {
        // 1. Update stock
        $sql_update_stock = "UPDATE PRODUK SET stok_semasa = stok_semasa + ? WHERE ID_produk = ?";
        $stmt_update = $conn->prepare($sql_update_stock);
        $stmt_update->bind_param("is", $jumlah_masuk, $id_produk);
        $stmt_update->execute();
        $stmt_update->close();

        // --- "STEAK" (FIX): "Slay" (fix) the "Joker" (SQL Bug) ---
        // 2. Insert transaction into the "steak" (correct) table
        $sql_insert_transaksi = "INSERT INTO transaksi_inventori 
                                (ID_produk, ID_staf, jenis_transaksi, jumlah_transaksi, tarikh_transaksi, harga_seunit, jumlah_harga, no_dokumen) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; // 8 columns
        
        $stmt_insert = $conn->prepare($sql_insert_transaksi);
        
        // The "vibe" (types) is "sssisdds" (8 types)
        $stmt_insert->bind_param("sssissds", 
            $id_produk, 
            $id_staf, 
            $jenis_transaksi, 
            $jumlah_masuk, 
            $tarikh_transaksi, 
            $harga_seunit, 
            $jumlah_harga, 
            $no_dokumen
        );
        // --- END OF "STEAK" (FIX) ---

        $stmt_insert->execute();
        $stmt_insert->close();

        // If both queries worked, "save" the changes
        $conn->commit();
        
        // Send a "vibe" (AJAX) success message back to the form
        header("Location: admin_stock_manual.php?success=Stok berjaya dikemaskini!");
        exit; 
        
    } catch (Exception $e) {
        // If *anything* failed, "undo" all changes
        $conn->rollback();
        // Send a "vibe" (AJAX) error message
        header("Location: admin_stock_manual.php?error=Gagal kemaskini stok: " . $e->getMessage());
        exit;
    }

    // Close connections
    $stmt_update->close();
    $stmt_insert->close();
    $conn->close();

} else {
    // If someone tries to access this file directly
    header("Location: admin_stock_manual.php?error=Akses tidak dibenarkan.");
    exit;
}
?>