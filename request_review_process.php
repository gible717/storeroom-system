<?php
// request_review_process.php - Handle request approval/rejection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require 'admin_auth_check.php';

// Check POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: manage_requests.php');
    exit;
}

// Get form data
$id_permohonan = $_POST['id_permohonan'] ?? null;
$id_pemohon = $_POST['id_pemohon'] ?? null;
$id_pelulus = $_SESSION['ID_staf'];
$action = $_POST['action'] ?? null;
$items = $_POST['items'] ?? [];
$tarikh_lulus = date('Y-m-d H:i:s');

// Validate data
if (!$id_permohonan || !$action || !$id_pemohon) {
    $_SESSION['error_msg'] = "Data tidak lengkap.";
    header('Location: manage_requests.php');
    exit;
}

// Handle rejection
if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE permohonan
                            SET status = 'Ditolak',
                                ID_pelulus = ?,
                                tarikh_lulus = ?
                            WHERE ID_permohonan = ? AND status = 'Baru'");
    $stmt->bind_param("ssi", $id_pelulus, $tarikh_lulus, $id_permohonan);
    $stmt->execute();

    // Check if request is AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "Permohonan #$id_permohonan telah berjaya ditolak."]);
        exit;
    }

    $_SESSION['success_msg'] = "Permohonan #$id_permohonan telah berjaya ditolak.";
    header('Location: manage_requests.php');
    exit;
}

// Handle approval with transaction
if ($action === 'approve') {
    $conn->begin_transaction();

    try {
        $at_least_one_item_approved = false;

        // Prepare statements
        $stmt_check_stock = $conn->prepare("SELECT baki_semasa, harga_seunit FROM barang WHERE no_kod = ? FOR UPDATE");
        $stmt_update_stock = $conn->prepare("UPDATE barang SET baki_semasa = baki_semasa - ? WHERE no_kod = ?");
        $stmt_update_request_item = $conn->prepare("UPDATE permohonan_barang SET kuantiti_lulus = ? WHERE ID_permohonan = ? AND no_kod = ?");
        $stmt_log_transaction = $conn->prepare(
            "INSERT INTO transaksi_stok (no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi, ID_rujukan_permohonan, tarikh_transaksi)
            VALUES (?, 'Keluar', ?, ?, ?, NOW())"
        );

        foreach ($items as $item) {
            $no_kod = $item['no_kod'];
            $kuantiti_lulus = (int)$item['kuantiti_lulus'];
            $perihal_stok = $item['perihal_stok'];

            if ($kuantiti_lulus > 0) {
                // Lock row and check stock
                $stmt_check_stock->bind_param("i", $no_kod);
                $stmt_check_stock->execute();
                $result_stock = $stmt_check_stock->get_result();
                $barang = $result_stock->fetch_assoc();

                if (!$barang || $barang['baki_semasa'] < $kuantiti_lulus) {
                    throw new Exception("Stok tidak mencukupi untuk " . htmlspecialchars($perihal_stok));
                }

                $at_least_one_item_approved = true;
                $baki_selepas_transaksi = $barang['baki_semasa'] - $kuantiti_lulus;

                // Update stock
                $stmt_update_stock->bind_param("ii", $kuantiti_lulus, $no_kod);
                $stmt_update_stock->execute();

                // Update approved quantity
                $stmt_update_request_item->bind_param("iii", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();

                // Log transaction
                $stmt_log_transaction->bind_param(
                    "iiii",
                    $no_kod,
                    $kuantiti_lulus,
                    $baki_selepas_transaksi,
                    $id_permohonan
                );
                $stmt_log_transaction->execute();
            } else {
                // Quantity set to 0
                $stmt_update_request_item->bind_param("iii", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();
            }
        }

        // Update request header
        $final_status = $at_least_one_item_approved ? 'Diluluskan' : 'Ditolak';
        $stmt_update_header = $conn->prepare("UPDATE permohonan
                                            SET status = ?,
                                                ID_pelulus = ?,
                                                tarikh_lulus = ?
                                            WHERE ID_permohonan = ? AND status = 'Baru'");
        $stmt_update_header->bind_param("sssi", $final_status, $id_pelulus, $tarikh_lulus, $id_permohonan);
        $stmt_update_header->execute();

        // Commit transaction
        $conn->commit();

        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "Permohonan #$id_permohonan telah berjaya diproses ($final_status).", 'status' => $final_status]);
            exit;
        }

        $_SESSION['success_msg'] = "Permohonan #$id_permohonan telah berjaya diproses ($final_status).";
        header('Location: manage_requests.php');
        exit;

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Gagal memproses permohonan. Ralat: " . $e->getMessage()]);
            exit;
        }

        $_SESSION['error_msg'] = "Gagal memproses permohonan. Ralat: " . $e->getMessage();
        header('Location: request_review.php?id=' . $id_permohonan);
        exit;
    }
}
?>