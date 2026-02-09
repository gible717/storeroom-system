<?php
// kewps8_receipt_process.php - Handle receipt acknowledgment
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

// Check if user is logged in and is a staff member
if (!isset($_SESSION['ID_staf']) || $_SESSION['is_admin'] == 1) {
    header('Location: login.php');
    exit;
}

// Validate CSRF token
csrf_check('kewps8_receipt.php');

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get data from form & session
    $id_permohonan = (int)$_POST['id_permohonan'];
    $items = $_POST['items'] ?? []; // The array of received quantities
    $id_staf = $_SESSION['ID_staf'];
    $tarikh_terima = date('Y-m-d');

    // Validation: Check if items were submitted
    if (empty($items)) {
        $_SESSION['error_msg'] = "Ralat: Tiada item untuk disahkan.";
        header('Location: request_list.php');
        exit;
    }

    // Begin database transaction
    $conn->begin_transaction();

    try {
        // Update each item with received quantity
        $sql_update_item = "UPDATE permohonan_barang SET kuantiti_diterima = ? WHERE ID_permohonan_barang = ? AND ID_permohonan = ?";
        $stmt_update_item = $conn->prepare($sql_update_item);

        foreach ($items as $id_item => $item_data) {
            $kuantiti_diterima = (int)$item_data['kuantiti_diterima'];
            $stmt_update_item->bind_param("iii", $kuantiti_diterima, $id_item, $id_permohonan);
            $stmt_update_item->execute();
        }
        $stmt_update_item->close();

        // Update request header status to 'Diterima'
        $sql_update_header = "UPDATE permohonan SET status = 'Diterima', tarikh_terima = ? WHERE ID_permohonan = ? AND ID_pemohon = ?";
        $stmt_update_header = $conn->prepare($sql_update_header);
        $stmt_update_header->bind_param("sis", $tarikh_terima, $id_permohonan, $id_staf);
        $stmt_update_header->execute();
        $stmt_update_header->close();

        // Commit the transaction
        $conn->commit();
        $_SESSION['success_msg'] = "Permohonan ID #$id_permohonan telah berjaya disahkan dan ditutup.";

    } catch (Exception $e) {
        // Something failed, roll back
        $conn->rollback();
        $_SESSION['error_msg'] = safeError("Gagal mengesahkan penerimaan.", $e->getMessage());
    }

    // Redirect back to the list
    header('Location: request_list.php');
    exit;
}