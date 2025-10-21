<?php
// FILE: supplier_edit_process.php
require 'admin_auth_check.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get data from the form
    $id_pembekal = $_POST['id_pembekal'];
    $nama_pembekal = $_POST['nama_pembekal'];
    $alamat = $_POST['alamat'];
    $no_telefon = $_POST['no_telefon'];
    $email = $_POST['email'];

    // 2. Validate essential data
    if (empty($id_pembekal) || empty($nama_pembekal)) {
        header("Location: supplier_edit.php?id=" . urlencode($id_pembekal) . "&error=" . urlencode("Nama Pembekal adalah wajib."));
        exit;
    }

    // 3. Prepare SQL UPDATE statement
    $sql = "UPDATE pembekal 
            SET nama_pembekal = ?, alamat = ?, no_telefon = ?, email = ?
            WHERE ID_pembekal = ?";
    
    $stmt = $conn->prepare($sql);

    // 4. Bind parameters
    // "sssss" = 5 strings
    $stmt->bind_param("sssss", $nama_pembekal, $alamat, $no_telefon, $email, $id_pembekal);

    // 5. Execute and check for errors
    if ($stmt->execute()) {
        // Success: redirect back to the supplier list
        header("Location: admin_suppliers.php?success=" . urlencode("Maklumat pembekal berjaya dikemaskini."));
    } else {
        // Fail: redirect back to the edit form with an error
        header("Location: supplier_edit.php?id=" . urlencode($id_pembekal) . "&error=" . urlencode("Gagal mengemaskini maklumat."));
    }

    // 6. Close resources
    $stmt->close();
    $conn->close();
    exit;

} else {
    // If someone tries to access this file directly, redirect them
    header("Location: admin_suppliers.php");
    exit;
}
?>