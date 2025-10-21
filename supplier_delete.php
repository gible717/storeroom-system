<?php
// FILE: supplier_delete.php
require 'admin_auth_check.php';

// 1. Get the ID from the URL
$id_pembekal = $_GET['id'] ?? null;
if (!$id_pembekal) {
    header("Location: admin_suppliers.php?error=" . urlencode("ID tidak sah."));
    exit;
}

// 2. Prepare SQL DELETE statement
// We are deleting the supplier where the ID matches
$sql = "DELETE FROM pembekal WHERE ID_pembekal = ?";
$stmt = $conn->prepare($sql);

// 3. Bind the ID parameter
$stmt->bind_param("s", $id_pembekal);

// 4. Execute and check for errors
try {
    if ($stmt->execute()) {
        // Success: redirect back to the supplier list
        header("Location: admin_suppliers.php?success=" . urlencode("Pembekal berjaya dipadam."));
    } else {
        // Fail: redirect back with a generic error
        header("Location: admin_suppliers.php?error=" . urlencode("Gagal memadam pembekal."));
    }
} catch (mysqli_sql_exception $e) {
    // Catch specific database errors, like a "foreign key constraint"
    // This happens if you try to delete a supplier that is already linked to an order
    if ($e->getCode() == 1451) {
        header("Location: admin_suppliers.php?error=" . urlencode("Gagal padam: Pembekal ini digunakan dalam rekod pesanan."));
    } else {
        header("Location: admin_suppliers.php?error=" . urlencode("Ralat database: " . $e->getMessage()));
    }
}

// 5. Close resources
$stmt->close();
$conn->close();
exit;
?>