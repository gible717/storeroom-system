<?php
// FILE: kewps8_receipt_process.php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in and is a staff member
if (!isset($_SESSION['ID_staf']) || $_SESSION['peranan'] != 'Staf') {
    header('Location: login.php');
    exit;
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get Data from Form & Session ---
    $id_permohonan = (int)$_POST['id_permohonan'];
    $items = $_POST['items'] ?? []; // The array of received quantities
    $id_staf = $_SESSION['ID_staf'];
    $tarikh_terima = date('Y-m-d'); // Current date

    // Validation: Check if items were submitted
    if (empty($items)) {
        $_SESSION['error_msg'] = "Ralat: Tiada item untuk disahkan.";
        header('Location: request_list.php');
        exit;
    }

    // --- 2. Begin Database Transaction ---
    $conn->begin_transaction();

    try {
        // --- 3. Loop and Update 'permohonan_barang' ---
        // We update each item with the 'kuantiti_diterima'
        $sql_update_item = "UPDATE permohonan_barang SET kuantiti_diterima = ? WHERE ID_permohonan_barang = ? AND ID_permohonan = ?";
        $stmt_update_item = $conn->prepare($sql_update_item);

        foreach ($items as $id_item => $item_data) {
            $kuantiti_diterima = (int)$item_data['kuantiti_diterima'];
            $stmt_update_item->bind_param("iii", $kuantiti_diterima, $id_item, $id_permohonan);
            $stmt_update_item->execute();
        }
        $stmt_update_item->close();

        // --- 4. Update 'permohonan' Header ---
        // We mark the whole request as 'Diterima' and set the date
        $sql_update_header = "UPDATE permohonan SET status = 'Diterima', tarikh_terima = ? WHERE ID_permohonan = ? AND ID_pemohon = ?";
        $stmt_update_header = $conn->prepare($sql_update_header);
        $stmt_update_header->bind_param("sis", $tarikh_terima, $id_permohonan, $id_staf);
        $stmt_update_header->execute();
        $stmt_update_header->close();

        // --- 5. Commit the Transaction ---
        $conn->commit();
        $_SESSION['success_msg'] = "Permohonan ID #$id_permohonan telah berjaya disahkan dan ditutup.";

    } catch (Exception $e) {
        // --- 6. Something failed. Roll back! ---
        $conn->rollback();
        $_SESSION['error_msg'] = "Gagal mengesahkan penerimaan. Ralat: " . $e->getMessage();
    }

    // --- 7. Redirect back to the list ---
    header('Location: request_list.php');
    exit;
}