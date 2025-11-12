<?php
// FILE: request_review_process.php (FINAL - Removing 'catatan_pelulus')

// 1. START THE SESSION (To be safe)
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// 2. TURN ON THE "FLOODLIGHTS"
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. FORCE DB ERRORS TO BE VISIBLE
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// ### END OF DEBUG SETUP ###


// --- The rest of the file ---
require 'admin_auth_check.php';

// --- 1. Get Data from POST ---
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: manage_requests.php');
    exit;
}

$id_permohonan = $_POST['id_permohonan'] ?? null;
$id_pemohon = $_POST['id_pemohon'] ?? null; // Staff who requested
$id_pelulus = $_SESSION['ID_staf']; // Admin who is approving
$action = $_POST['action'] ?? null; // 'approve' or 'reject'
$items = $_POST['items'] ?? [];
// $catatan_pelulus = $_POST['catatan_pelulus'] ?? null; // We will not use this
$tarikh_lulus = date('Y-m-d H:i:s');

// --- 2. Validation ---
if (!$id_permohonan || !$action || !$id_pemohon) {
    $_SESSION['error_msg'] = "Data tidak lengkap.";
    header('Location: manage_requests.php');
    exit;
}

// --- 3. Handle REJECT action (Simple) ---
if ($action === 'reject') {
    
    // ### FIX: Removed 'catatan_pelulus' from the query ###
    $stmt = $conn->prepare("UPDATE permohonan 
                            SET status = 'Ditolak', 
                                ID_pelulus = ?, 
                                tarikh_lulus = ? 
                            WHERE ID_permohonan = ? AND status = 'Baru'");
    // ### FIX: Changed bind_param string from 'sssi' to 'ssi' ###
    $stmt->bind_param("ssi", $id_pelulus, $tarikh_lulus, $id_permohonan);
    $stmt->execute();
    
    $_SESSION['success_msg'] = "Permohonan #$id_permohonan telah berjaya ditolak.";
    header('Location: manage_requests.php');
    exit;
}

// --- 4. Handle APPROVE action (Complex Transaction) ---
if ($action === 'approve') {
    
    $conn->begin_transaction();
    
    // ### NOTE: The 'try {' is COMMENTED OUT on purpose to find the bug ###
    try {
        
        $at_least_one_item_approved = false;
        
        // Prepare statements
        $stmt_check_stock = $conn->prepare("SELECT baki_semasa, harga_seunit FROM barang WHERE no_kod = ? FOR UPDATE");
        $stmt_update_stock = $conn->prepare("UPDATE barang SET baki_semasa = baki_semasa - ? WHERE no_kod = ?");
        $stmt_update_request_item = $conn->prepare("UPDATE permohonan_barang SET kuantiti_lulus = ? WHERE ID_permohonan = ? AND no_kod = ?");

        // Using your 'transaksi_stok' table
        $stmt_log_transaction = $conn->prepare(
            "INSERT INTO transaksi_stok (no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi, ID_rujukan_permohonan, tarikh_transaksi) 
            VALUES (?, 'Keluar', ?, ?, ?, NOW())"
        );

        foreach ($items as $item) {
            $no_kod = $item['no_kod'];
            $kuantiti_lulus = (int)$item['kuantiti_lulus'];
            $perihal_stok = $item['perihal_stok']; // Get name for error
            
            if ($kuantiti_lulus > 0) {
                // 4a. Lock row and check stock
                $stmt_check_stock->bind_param("i", $no_kod);
                $stmt_check_stock->execute();
                $result_stock = $stmt_check_stock->get_result();
                $barang = $result_stock->fetch_assoc();
                
                if (!$barang || $barang['baki_semasa'] < $kuantiti_lulus) {
                    throw new Exception("Stok tidak mencukupi untuk " . htmlspecialchars($perihal_stok));
                }
                
                $at_least_one_item_approved = true;
                $baki_selepas_transaksi = $barang['baki_semasa'] - $kuantiti_lulus;

                // 4b. Update the stock in 'barang' table
                $stmt_update_stock->bind_param("ii", $kuantiti_lulus, $no_kod);
                $stmt_update_stock->execute();
                
                // 4c. Update the 'kuantiti_lulus' in 'permohonan_barang'
                $stmt_update_request_item->bind_param("iii", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();
                
                // 4d. Log this in 'transaksi_stok'
                $stmt_log_transaction->bind_param(
                    "iiii", 
                    $no_kod, 
                    $kuantiti_lulus,
                    $baki_selepas_transaksi,
                    $id_permohonan
                );
                $stmt_log_transaction->execute();
            } else {
                // If admin set quantity to 0, just record it
                $stmt_update_request_item->bind_param("iii", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();
            }
        }
        
        // 4e. Update the main 'permohonan' header
        $final_status = $at_least_one_item_approved ? 'Diluluskan' : 'Ditolak';
        
        // ### FIX: Removed 'catatan_pelulus' from the query ###
        $stmt_update_header = $conn->prepare("UPDATE permohonan 
                                            SET status = ?, 
                                                ID_pelulus = ?, 
                                                tarikh_lulus = ? 
                                            WHERE ID_permohonan = ? AND status = 'Baru'");
        // ### FIX: Changed bind_param string from 'ssssi' to 'sssi' ###
        $stmt_update_header->bind_param("sssi", $final_status, $id_pelulus, $tarikh_lulus, $id_permohonan);
        $stmt_update_header->execute();

        // 4f. Commit the transaction
        $conn->commit();
        
        // If it gets here, it worked!
        $_SESSION['success_msg'] = "Permohonan #$id_permohonan telah berjaya diproses ($final_status).";
        header('Location: manage_requests.php');
        exit;

    // ### NOTE: The 'catch' block is COMMENTED OUT on purpose to find the bug ###
    } catch (Exception $e) {
        // 4g. If anything fails, roll back
        $conn->rollback();
        $_SESSION['error_msg'] = "Gagal memproses permohonan. Ralat: " . $e->getMessage();
        header('Location: request_review.php?id=' . $id_permohonan);
        exit;
    }
}
?>