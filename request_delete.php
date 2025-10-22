<?php
// FILE: request_delete.php

// Use staff_auth_check, which also starts session and connects to DB
require 'staff_auth_check.php';

// Get the ID of the request to delete
$id_permohonan = $_GET['id'] ?? null;
// Get the ID of the logged-in staff
$id_staf = $_SESSION['ID_staf'] ?? null;

// Security: Check if we have both IDs
if (empty($id_permohonan) || empty($id_staf)) {
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
        AND ID_staf = ? 
        AND status = 'Belum Diproses'";

$stmt = $conn->prepare($sql);
// Assuming ID_permohonan is an integer (i) and ID_staf is a string (s)
// If ID_permohonan is a string like 'P001', use "ss"
$stmt->bind_param("is", $id_permohonan, $id_staf);

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
    // Generic database error
    $msg = urlencode("Gagal memadam permohonan. Ralat pangkalan data.");
    header("Location: request_list.php?error=" . $msg);
}

$stmt->close();
$conn->close();
exit;
?>