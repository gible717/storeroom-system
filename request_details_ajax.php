<?php
// FILE: request_details_ajax.php
require 'staff_auth_check.php'; // Ensures the user is logged in

// We will return JSON
header('Content-Type: application/json');

$id_staf = $_SESSION['ID_staf'];
$id_permohonan = $_GET['id'] ?? null;

if (!$id_permohonan) {
    echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
    exit;
}

// --- 1. Security Check & Get Header Info ---
// Make sure this user owns this request
$stmt = $conn->prepare("SELECT * FROM permohonan 
                        WHERE ID_permohonan = ? AND ID_pemohon = ?");
$stmt->bind_param("is", $id_permohonan, $id_staf);
$stmt->execute();
$request_header = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request_header) {
    echo json_encode(['success' => false, 'message' => 'Permohonan tidak dijumpai.']);
    exit;
}

// --- 2. Get Item List for this Request ---
$items_in_request = [];
$stmt_items = $conn->prepare("SELECT pb.no_kod, pb.kuantiti_mohon, b.perihal_stok 
                            FROM permohonan_barang pb 
                            JOIN barang b ON pb.no_kod = b.no_kod 
                            WHERE pb.ID_permohonan = ?");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
while ($row = $result_items->fetch_assoc()) {
    $items_in_request[] = $row;
}
$stmt_items->close();

// --- 3. Send the Data as JSON ---
echo json_encode([
    'success' => true,
    'header' => $request_header,
    'items' => $items_in_request
]);

$conn->close();
exit;
?>