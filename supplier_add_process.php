<?php
// FILE: supplier_add_process.php
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
        header("Location: supplier_add.php?error=" . urlencode("ID Pembekal dan Nama Pembekal adalah wajib."));
        exit;
    }

    // 3. Prepare SQL statement to prevent SQL injection
    $sql = "INSERT INTO pembekal (ID_pembekal, nama_pembekal, alamat, no_telefon, email) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);

    // 4. Bind parameters
    // "sssss" means all 5 parameters are strings
    $stmt->bind_param("sssss", $id_pembekal, $nama_pembekal, $alamat, $no_telefon, $email);

    // 5. Execute and check for errors
    try {
        if ($stmt->execute()) {
            // Success: redirect back to the supplier list
            header("Location: admin_suppliers.php?success=" . urlencode("Pembekal baru berjaya ditambah."));
        } else {
            // General error
            header("Location: supplier_add.php?error=" . urlencode("Gagal menambah pembekal. Sila cuba lagi."));
        }
    } catch (mysqli_sql_exception $e) {
        // Check for specific duplicate key error (error code 1062)
        if ($e->getCode() == 1062) {
            header("Location: supplier_add.php?error=" . urlencode("Gagal: ID Pembekal '$id_pembekal' sudah wujud."));
        } else {
            header("Location: supplier_add.php?error=" . urlencode("Ralat database: " . $e->getMessage()));
        }
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