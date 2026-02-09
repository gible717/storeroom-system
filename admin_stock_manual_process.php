<?php
/**
 * Manual Stock Adjustment Processing
 *
 * PURPOSE:
 * Handles manual stock IN adjustments by admin.
 * Used for restocking from suppliers or manual inventory corrections.
 *
 * WORKFLOW:
 * 1. Validate input (quantity > 0, product exists)
 * 2. Begin database transaction
 * 3. UPDATE barang.baki_semasa (increase by quantity)
 * 4. Get updated balance for accurate logging
 * 5. INSERT transaction log to transaksi_stok (jenis='Masuk')
 * 6. Commit transaction and redirect
 *
 * INPUT: POST data with ID_produk, jumlah_masuk, no_dokumen (supplier doc reference)
 * OUTPUT: Redirect to admin_products.php with success/error message
 *
 * TABLES AFFECTED:
 * - barang (UPDATE baki_semasa)
 * - transaksi_stok (INSERT transaction log with no request reference)
 *
 * NOTE: This process is separate from request approval.
 *       ID_rujukan_permohonan will be NULL as this is manual adjustment.
 */

require_once 'admin_auth_check.php';
require_once 'csrf.php';

// Validate CSRF token
csrf_check('admin_stock_manual.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_produk = trim($_POST['ID_produk'] ?? '');
    $jumlah_masuk = (int)$_POST['jumlah_masuk'];
    $no_dokumen = trim($_POST['no_dokumen'] ?? '');
    $id_staf = $userID;

    // Validate input
    if ($jumlah_masuk <= 0 || empty($id_produk)) {
        header("Location: admin_stock_manual.php?error=Data tidak lengkap. Sila isi semua medan.");
        exit;
    }

    $conn->begin_transaction();

    try {
        // Update product stock in barang table
        $sql_update_stock = "UPDATE barang SET baki_semasa = baki_semasa + ? WHERE no_kod = ?";
        $stmt_update = $conn->prepare($sql_update_stock);
        $stmt_update->bind_param("is", $jumlah_masuk, $id_produk);
        $stmt_update->execute();
        $stmt_update->close();

        // Get the stock balance after transaction for logging
        $sql_get_baki = "SELECT baki_semasa FROM barang WHERE no_kod = ?";
        $stmt_baki = $conn->prepare($sql_get_baki);
        $stmt_baki->bind_param("s", $id_produk);
        $stmt_baki->execute();
        $result_baki = $stmt_baki->get_result();
        $baki_selepas = $result_baki->fetch_assoc()['baki_semasa'];
        $stmt_baki->close();

        // Insert transaction record into transaksi_stok
        $sql_insert_transaksi = "INSERT INTO transaksi_stok
                                (no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi, tarikh_transaksi, terima_dari_keluar_kepada, ID_pegawai)
                                VALUES (?, 'Masuk', ?, ?, NOW(), ?, ?)";

        $stmt_insert = $conn->prepare($sql_insert_transaksi);
        $stmt_insert->bind_param("siiss",
            $id_produk,
            $jumlah_masuk,
            $baki_selepas,
            $no_dokumen,
            $id_staf
        );

        $stmt_insert->execute();
        $stmt_insert->close();

        $conn->commit();

        header("Location: admin_products.php?success=Stok berjaya dikemaskini!");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin_stock_manual.php?error=" . urlencode(safeError("Gagal kemaskini stok.", $e->getMessage())));
        exit;
    }

} else {
    header("Location: admin_stock_manual.php?error=Akses tidak dibenarkan.");
    exit;
}
?>
