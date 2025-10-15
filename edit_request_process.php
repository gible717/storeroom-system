<?php
// FILE: edit_request_process.php
require 'auth_check.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: request_list.php');
    exit;
}

// 1. Get the data from the form
$request_id = $_POST['id_permohonan'] ?? null;
$new_quantity = (int)($_POST['jumlah_diminta'] ?? 0);
$new_notes = trim($_POST['catatan'] ?? '');

// Validation
if (!$request_id || $new_quantity <= 0) {
    header("Location: request_list.php?error=" . urlencode("Data tidak sah."));
    exit;
}

// 2. CRITICAL SECURITY CHECK: Verify the request belongs to the user and is still pending before updating.
// This prevents a user from editing a request that's already been processed.
$stmt = $conn->prepare("SELECT ID_produk FROM permohonan WHERE ID_permohonan = ? AND ID_staf = ? AND status = 'Belum Diproses'");
$stmt->bind_param('is', $request_id, $userID);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    // If it doesn't meet the criteria, don't update it.
    header("Location: request_list.php?error=" . urlencode("Permohonan ini tidak lagi boleh dikemaskini."));
    exit;
}

// 3. Prepare and execute the UPDATE statement
$sql = "UPDATE permohonan SET jumlah_diminta = ?, catatan = ? WHERE ID_permohonan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('isi', $new_quantity, $new_notes, $request_id);

if ($stmt->execute()) {
    header("Location: request_list.php?success=" . urlencode("Permohonan telah berjaya dikemaskini."));
} else {
    header("Location: edit_request.php?id=" . $request_id . "&error=" . urlencode("Gagal mengemaskini permohonan."));
}
exit;
?>