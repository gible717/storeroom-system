<?php
// FILE: update_request_status.php
require 'auth_check.php';

// Security: Only admins can perform this action.
if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php");
    exit;
}

// 1. Get the request ID and the action from the URL (e.g., ?id=5&action=approve)
$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

if (!$request_id || !in_array($action, ['approve', 'reject'])) {
    // If ID or action is missing/invalid, redirect back.
    header("Location: manage_requests.php?error=" . urlencode("Tindakan tidak sah."));
    exit;
}

// 2. Fetch the request details to ensure it's still pending
$stmt = $conn->prepare("SELECT ID_produk, jumlah_diminta, status FROM permohonan WHERE ID_permohonan = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

// Check if the request exists and hasn't already been processed
if (!$request || $request['status'] !== 'Belum Diproses') {
    header("Location: manage_requests.php?error=" . urlencode("Permohonan ini telah diproses atau tidak wujud."));
    exit;
}

// 3. Perform the requested action
if ($action === 'approve') {
    // APPROVE LOGIC
    $new_status = 'Diluluskan';

    // CRITICAL STEP: Deduct the stock from the 'produk' table
    $update_stock_sql = "UPDATE produk SET stok_semasa = stok_semasa - ? WHERE ID_produk = ?";
    $stmt_stock = $conn->prepare($update_stock_sql);
    $stmt_stock->bind_param('is', $request['jumlah_diminta'], $request['ID_produk']);
    $stmt_stock->execute();

} elseif ($action === 'reject') {
    // REJECT LOGIC
    $new_status = 'Ditolak';
    // No stock change is needed if the request is rejected.
}

// 4. Update the request status in the 'permohonan' table
$update_status_sql = "UPDATE permohonan SET status = ?, ID_staf_pelulus = ? WHERE ID_permohonan = ?";
$stmt_status = $conn->prepare($update_status_sql);
// $userID is the admin's ID from auth_check.php, recording who took the action
$stmt_status->bind_param('ssi', $new_status, $userID, $request_id); 

if ($stmt_status->execute()) {
    // Success! Redirect back with a success message.
    header("Location: manage_requests.php?success=" . urlencode("Status permohonan telah dikemaskini."));
} else {
    // Fail! Redirect back with an error message.
    header("Location: manage_requests.php?error=" . urlencode("Gagal mengemaskini status."));
}
exit;
?>