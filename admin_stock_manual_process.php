<?php
// FILE: admin_stock_manual_process.php
session_start();
require 'db.php';

// "Boring" (but vital) Security Check
if (!isset($_SESSION['id_staf']) || $_SESSION['peranan'] != 'Admin') {
    // Redirect to login if not an Admin
    header("Location: login.php?error=Sila log masuk sebagai Admin.");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get data from form and sanitize it
    $id_produk = $conn->real_escape_string($_POST['id_produk']);
    $jumlah_masuk = (int)$_POST['jumlah_masuk']; // Cast to integer for safety
    $id_staf = $conn->real_escape_string($_POST['id_staf']);
    $no_dokumen = $conn->real_escape_string($_POST['no_dokumen']);
    $jenis_transaksi = 'Stok_Masuk';
    $tarikh_transaksi = date('Y-m-d H:i:s'); // Current date and time

    // Validate data
    if ($jumlah_masuk <= 0 || empty($id_produk) || empty($no_dokumen)) {
        header("Location: admin_stock_manual.php?error=Data tidak lengkap. Sila isi semua medan.");
        exit();
    }

    // --- This is the "4x4" (Strategist) move ---
    // We use a "transaction" to make sure BOTH database queries pass, or BOTH fail.
    // This prevents "ghost" stock (data mismatch).
    
    $conn->begin_transaction();

    try {
        // 1. "Boring" Query 1: Update the 'stok_semasa' in the PRODUK table
        $sql_update_stock = "UPDATE PRODUK SET stok_semasa = stok_semasa + ? WHERE ID_produk = ?";
        $stmt_update = $conn->prepare($sql_update_stock);
        $stmt_update->bind_param("is", $jumlah_masuk, $id_produk);
        $stmt_update->execute();

        // 2. "Boring" Query 2: Create a new 'Stok_Masuk' record for the audit trail
        // This is what makes your "Laporan Transaksi" work!
        $sql_insert_transaksi = "INSERT INTO TRANSAKSI_PRODUK (ID_produk, ID_staf, jenis_transaksi, jumlah_transaksi, tarikh_transaksi, no_dokumen) 
                                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert_transaksi);
        $stmt_insert->bind_param("sssisd", $id_produk, $id_staf, $jenis_transaksi, $jumlah_masuk, $tarikh_transaksi, $no_dokumen);
        $stmt_insert->execute();

        // If both queries worked, "save" the changes
        $conn->commit();
        
        // Send a "vibe" (AJAX) success message back to the form
        header("Location: admin_stock_manual.php?success=Stok berjaya dikemaskini!");
        
    } catch (Exception $e) {
        // If *anything* failed, "undo" all changes
        $conn->rollback();
        // Send a "vibe" (AJAX) error message
        header("Location: admin_stock_manual.php?error=Gagal kemaskini stok: " . $e->getMessage());
    }

    // Close connections
    $stmt_update->close();
    $stmt_insert->close();
    $conn->close();

} else {
    // If someone tries to access this file directly
    header("Location: admin_stock_manual.php?error=Akses tidak dibenarkan.");
}
?>