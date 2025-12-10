<?php
// admin_stock_manual_process.php - Process stock update

require_once 'admin_auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_produk = $conn->real_escape_string($_POST['ID_produk']);
    $jumlah_masuk = (int)$_POST['jumlah_masuk'];
    $no_dokumen = $conn->real_escape_string($_POST['no_dokumen']);
    $id_staf = $userID;
    $jenis_transaksi = 'Stok_Masuk';
    $tarikh_transaksi = date('Y-m-d H:i:s');

    // Set null for price fields (not applicable for stock in)
    $harga_seunit = null;
    $jumlah_harga = null;

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

        // Insert transaction record
        $sql_insert_transaksi = "INSERT INTO transaksi_inventori
                                (ID_produk, ID_staf, jenis_transaksi, jumlah_transaksi, tarikh_transaksi, harga_seunit, jumlah_harga, no_dokumen)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $conn->prepare($sql_insert_transaksi);
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

        $stmt_insert->execute();
        $stmt_insert->close();

        $conn->commit();

        header("Location: admin_stock_manual.php?success=Stok berjaya dikemaskini!");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin_stock_manual.php?error=Gagal kemaskini stok: " . $e->getMessage());
        exit;
    }

} else {
    header("Location: admin_stock_manual.php?error=Akses tidak dibenarkan.");
    exit;
}
?>
