<?php
// FILE: request_delete.php

// Use staff_auth_check, which also starts session and connects to DB
require 'staff_auth_check.php';

// Get the ID of the request to delete
$id_permohonan = $_GET['id'] ?? null;
// Get the ID of the logged-in staff
$id_pemohon = $_SESSION['ID_staf'] ?? null;

// Security: Check if we have both IDs
if (empty($id_permohonan) || empty($id_pemohon)) {
    $msg = urlencode("Permintaan tidak sah.");
    header("Location: request_list.php?error=" . $msg);
    exit;
}

// Prepare the SQL
// We add three checks for security:
// 1. Match the request ID
// 2. Match the logged-in staff ID (users can only delete their own)
// 3. Match the status (users can only delete 'Belum Diproses')
$sql = "DELETE FROM permohonan 
        WHERE ID_permohonan = ? 
        AND ID_pemohon = ? 
        AND status = 'Baru'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_permohonan, $id_pemohon);

if ($stmt->execute()) {
    // Check if a row was actually deleted
    if ($stmt->affected_rows > 0) {
        $msg = urlencode("Permohonan berjaya dipadam.");
        header("Location: request_list.php?success=" . $msg);
    } else {
        // This means the request was not 'Belum Diproses' or didn't belong to the user
        $msg = urlencode("Gagal memadam permohonan. Ia mungkin telah diluluskan oleh admin.");
        header("Location: request_list.php?error=" . $msg);
    }
} else {
// Example for an error
$_SESSION['error_msg'] = "Permintaan tidak sah.";
header("Location: request_list.php");

// Example for the success
$_SESSION['success_msg'] = "Permohonan berjaya dipadam.";
header("Location: request_list.php");

// Example for the "fail" message
$_SESSION['error_msg'] = "Gagal memadam permohonan. Ia mungkin telah diluluskan oleh admin.";
header("Location: request_list.php");
}
    $msg = urlencode("Ralat semasa memadam permohonan: " . $stmt->error);
    header("Location: request_list.php?error=" . $msg);
    
$stmt->close();
$conn->close();
exit;
?>