// kewps8_approval_process.php - Handle stock request approval/rejection
<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

// Check if user is logged in and is an Admin
if (!isset($_SESSION['ID_staf']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Validate CSRF token
csrf_check('kewps8_approval.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get All Data from Form & Session ---
    $id_permohonan = (int)$_POST['id_permohonan'];
    $action = $_POST['action']; // "Luluskan" or "Ditolak"
    $items = $_POST['items'] ?? []; // The array of approved quantities
    
    // Admin details from session
    $id_pelulus = $_SESSION['ID_staf'];
    $nama_pelulus = $_SESSION['nama'];
    $tarikh_lulus = date('Y-m-d');

    // Check if admin is trying to approve their own request
    $stmt_check = $conn->prepare("SELECT ID_pemohon FROM permohonan WHERE ID_permohonan = ?");
    $stmt_check->bind_param("i", $id_permohonan);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if ($check_result && $check_result['ID_pemohon'] == $id_pelulus) {
        $_SESSION['error_msg'] = "Ralat: Anda tidak boleh meluluskan permohonan anda sendiri.";
        header('Location: admin_request_list.php');
        exit;
    }

    // We must fetch the admin's 'jawatan' from the DB
    $stmt_admin = $conn->prepare("SELECT jawatan FROM staf WHERE ID_staf = ?");
    $stmt_admin->bind_param("s", $id_pelulus);
    $stmt_admin->execute();
    $admin_user = $stmt_admin->get_result()->fetch_assoc();
    $jawatan_pelulus = $admin_user['jawatan'];
    $stmt_admin->close();

    // --- 2. Begin Database Transaction ---
    $conn->begin_transaction();

    try {
        if ($action == "Ditolak") {
            // --- 3A. ACTION: REJECT ---
            // This is simple. Just update the status.
            $sql_reject = "UPDATE permohonan SET 
                                status = 'Ditolak',
                                ID_pelulus = ?,
                                nama_pelulus = ?,
                                jawatan_pelulus = ?,
                                tarikh_lulus = ?
                        WHERE ID_permohonan = ?";
            
            $stmt_reject = $conn->prepare($sql_reject);
            $stmt_reject->bind_param("ssssi", $id_pelulus, $nama_pelulus, $jawatan_pelulus, $tarikh_lulus, $id_permohonan);
            $stmt_reject->execute();
            $stmt_reject->close();

        } elseif ($action == "Luluskan") {
            // --- 3B. ACTION: APPROVE (Complex) ---
            
            // Step 1: Loop through all items to update quantities
            $sql_update_item = "UPDATE permohonan_barang SET kuantiti_lulus = ? WHERE ID_permohonan_barang = ?";
            $stmt_update_item = $conn->prepare($sql_update_item);

            // Step 2: Update the main stock (baki_semasa)
            $sql_update_stock = "UPDATE barang SET baki_semasa = baki_semasa - ? WHERE no_kod = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);

            // Step 3: Prepare transaction log statement (for KEW.PS-3 compliance)
            // Get department/unit name from permohonan table (for Terima Daripada/Keluar Kepada column)
            $stmt_get_dept = $conn->prepare("SELECT j.nama_jabatan FROM permohonan p JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan WHERE p.ID_permohonan = ?");
            $stmt_get_dept->bind_param("i", $id_permohonan);
            $stmt_get_dept->execute();
            $dept_data = $stmt_get_dept->get_result()->fetch_assoc();
            $dept_name = $dept_data['nama_jabatan'] ?? '-';
            $stmt_get_dept->close();

            $sql_log_transaksi = "INSERT INTO transaksi_stok
                                (tarikh_transaksi, terima_dari_keluar_kepada, no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi, ID_rujukan_permohonan, ID_pegawai)
                                VALUES (NOW(), ?, ?, 'Keluar', ?, (SELECT baki_semasa FROM barang WHERE no_kod = ?), ?, ?)";
            $stmt_log_transaksi = $conn->prepare($sql_log_transaksi);

            foreach ($items as $id_item => $item_data) {
                $kuantiti_lulus = (int)$item_data['kuantiti_lulus'];

                // We need to find the no_kod for this item
                $stmt_get_nokod = $conn->prepare("SELECT no_kod FROM permohonan_barang WHERE ID_permohonan_barang = ?");
                $stmt_get_nokod->bind_param("i", $id_item);
                $stmt_get_nokod->execute();
                $item_details = $stmt_get_nokod->get_result()->fetch_assoc();
                $no_kod = $item_details['no_kod'];
                $stmt_get_nokod->close();

                if ($kuantiti_lulus > 0) {
                    // Update 'permohonan_barang' with approved quantity
                    $stmt_update_item->bind_param("ii", $kuantiti_lulus, $id_item);
                    $stmt_update_item->execute();

                    // Update 'barang' (stock-out)
                    $stmt_update_stock->bind_param("is", $kuantiti_lulus, $no_kod);
                    $stmt_update_stock->execute();

                    // Log in 'transaksi_stok' (for KEW.PS-3)
                    // Parameters: terima_dari_keluar_kepada (department/unit name), no_kod, kuantiti, no_kod (for SELECT), ID_rujukan_permohonan, ID_pegawai (approver)
                    $stmt_log_transaksi->bind_param("ssiiss", $dept_name, $no_kod, $kuantiti_lulus, $no_kod, $id_permohonan, $id_pelulus);
                    $stmt_log_transaksi->execute();
                } else {
                    // If admin approved 0, just update the item status
                    $stmt_update_item->bind_param("ii", $kuantiti_lulus, $id_item);
                    $stmt_update_item->execute();
                }
            }
            $stmt_update_item->close();
            $stmt_update_stock->close();
            $stmt_log_transaksi->close();

            // Step 4: Update the main 'permohonan' status
            $sql_approve = "UPDATE permohonan SET 
                                status = 'Diluluskan',
                                ID_pelulus = ?,
                                nama_pelulus = ?,
                                jawatan_pelulus = ?,
                                tarikh_lulus = ?
                            WHERE ID_permohonan = ?";
            
            $stmt_approve = $conn->prepare($sql_approve);
            $stmt_approve->bind_param("ssssi", $id_pelulus, $nama_pelulus, $jawatan_pelulus, $tarikh_lulus, $id_permohonan);
            $stmt_approve->execute();
            $stmt_approve->close();
        }

        // --- 4. Commit the Transaction ---
        $conn->commit();
        $_SESSION['success_msg'] = "Permohonan ID #$id_permohonan telah berjaya dikemaskini.";

    } catch (Exception $e) {
        // --- 5. Something failed. Roll back! ---
        $conn->rollback();
        $_SESSION['error_msg'] = safeError("Gagal mengemaskini permohonan.", $e->getMessage());
    }

    // --- 6. Redirect back to the list ---
    header('Location: admin_request_list.php');
    exit;

} else {
    // Not a POST request
    header('Location: admin_request_list.php');
    exit;
}
?>