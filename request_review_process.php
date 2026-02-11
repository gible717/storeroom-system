<?php
/**
 * Request Review Processing
 *
 * PURPOSE:
 * Handles admin approval or rejection of staff requests.
 * Updates stock levels and logs transactions when approving.
 *
 * WORKFLOW (APPROVAL):
 * 1. Validate admin is not approving their own request
 * 2. Begin database transaction
 * 3. For each item: Check stock, update barang.baki_semasa
 * 4. Update permohonan_barang.kuantiti_lulus
 * 5. Log to transaksi_stok (jenis='Keluar')
 * 6. Update permohonan.status to 'Diluluskan'
 * 7. Commit transaction
 *
 * WORKFLOW (REJECTION):
 * 1. Validate admin is not rejecting their own request
 * 2. Update permohonan.status to 'Ditolak'
 * 3. No stock changes or transaction logs
 *
 * INPUT: POST data with id_permohonan, action (approve/reject), items array
 * OUTPUT: Redirect to manage_requests.php or JSON response (AJAX)
 *
 * TABLES AFFECTED:
 * - permohonan (UPDATE status, ID_pelulus, tarikh_lulus)
 * - permohonan_barang (UPDATE kuantiti_lulus)
 * - barang (UPDATE baki_semasa - on approval only)
 * - transaksi_stok (INSERT transaction log - on approval only)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting handled by global error_handler.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require 'admin_auth_check.php';
require_once 'csrf.php';

// Check POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: manage_requests.php');
    exit;
}

// Validate CSRF token
csrf_check('manage_requests.php');

// Get form data
$id_permohonan = $_POST['id_permohonan'] ?? null;
$id_pemohon = $_POST['id_pemohon'] ?? null;
$id_pelulus = $_SESSION['ID_staf'];
$action = $_POST['action'] ?? null;
$items = $_POST['items'] ?? [];
$tarikh_lulus = date('Y-m-d H:i:s');
$admin_remarks = trim($_POST['catatan_pelulus'] ?? ''); // Admin's remarks/notes (from catatan_pelulus field)

// Validate data
if (!$id_permohonan || !$action || !$id_pemohon) {
    $_SESSION['error_msg'] = "Data tidak lengkap.";
    header('Location: manage_requests.php');
    exit;
}

// Prevent admin from approving/rejecting their own request
if ($id_pemohon === $id_pelulus) {
    // Customize message based on action
    if ($action === 'approve') {
        $error_message = "Anda tidak boleh meluluskan permohonan anda sendiri. Kelulusan mesti dibuat oleh admin lain.";
    } else {
        $error_message = "Anda tidak boleh menolak permohonan anda sendiri. Penolakan mesti dibuat oleh admin lain.";
    }

    // Check if request is AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }

    $_SESSION['error_msg'] = $error_message;
    header('Location: request_review.php?id=' . $id_permohonan);
    exit;
}

// Handle rejection
if ($action === 'reject') {
    // Get admin's name and position for logging
    $stmt_admin = $conn->prepare("SELECT nama, jawatan FROM staf WHERE ID_staf = ?");
    $stmt_admin->bind_param("s", $id_pelulus);
    $stmt_admin->execute();
    $admin_data = $stmt_admin->get_result()->fetch_assoc();
    $nama_pelulus = $admin_data['nama'];
    $jawatan_pelulus = $admin_data['jawatan'];
    $stmt_admin->close();

    $stmt = $conn->prepare("UPDATE permohonan
                            SET status = 'Ditolak',
                                ID_pelulus = ?,
                                nama_pelulus = ?,
                                jawatan_pelulus = ?,
                                tarikh_lulus = ?,
                                catatan_admin = ?
                            WHERE ID_permohonan = ? AND status = 'Baru'");
    $stmt->bind_param("sssssi", $id_pelulus, $nama_pelulus, $jawatan_pelulus, $tarikh_lulus, $admin_remarks, $id_permohonan);
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
                $stmt_check_stock->bind_param("s", $no_kod);
                $stmt_check_stock->execute();
                $result_stock = $stmt_check_stock->get_result();
                $barang = $result_stock->fetch_assoc();

                if (!$barang || $barang['baki_semasa'] < $kuantiti_lulus) {
                    throw new Exception("Stok tidak mencukupi untuk " . htmlspecialchars($perihal_stok));
                }

                $at_least_one_item_approved = true;
                $baki_selepas_transaksi = $barang['baki_semasa'] - $kuantiti_lulus;

                // Update stock
                $stmt_update_stock->bind_param("is", $kuantiti_lulus, $no_kod);
                $stmt_update_stock->execute();

                // Update approved quantity
                $stmt_update_request_item->bind_param("iis", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();

                // Log transaction
                $stmt_log_transaction->bind_param(
                    "siii",
                    $no_kod,
                    $kuantiti_lulus,
                    $baki_selepas_transaksi,
                    $id_permohonan
                );
                $stmt_log_transaction->execute();
            } else {
                // Quantity set to 0
                $stmt_update_request_item->bind_param("iis", $kuantiti_lulus, $id_permohonan, $no_kod);
                $stmt_update_request_item->execute();
            }
        }

        // Update request header
        $final_status = $at_least_one_item_approved ? 'Diluluskan' : 'Ditolak';

        // Get admin's name and position for logging
        $stmt_admin = $conn->prepare("SELECT nama, jawatan FROM staf WHERE ID_staf = ?");
        $stmt_admin->bind_param("s", $id_pelulus);
        $stmt_admin->execute();
        $admin_data = $stmt_admin->get_result()->fetch_assoc();
        $nama_pelulus = $admin_data['nama'];
        $jawatan_pelulus = $admin_data['jawatan'];
        $stmt_admin->close();

        $stmt_update_header = $conn->prepare("UPDATE permohonan
                                            SET status = ?,
                                                ID_pelulus = ?,
                                                nama_pelulus = ?,
                                                jawatan_pelulus = ?,
                                                tarikh_lulus = ?,
                                                catatan_admin = ?
                                            WHERE ID_permohonan = ? AND status = 'Baru'");
        $stmt_update_header->bind_param("ssssssi", $final_status, $id_pelulus, $nama_pelulus, $jawatan_pelulus, $tarikh_lulus, $admin_remarks, $id_permohonan);
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
            echo json_encode(['success' => false, 'message' => safeError("Gagal memproses permohonan.", $e->getMessage())]);
            exit;
        }

        $_SESSION['error_msg'] = safeError("Gagal memproses permohonan.", $e->getMessage());
        header('Location: request_review.php?id=' . $id_permohonan);
        exit;
    }
}
?>