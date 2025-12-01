<?php
// request_details_ajax.php - Get request details (AJAX)

require 'staff_auth_check.php';

header('Content-Type: application/json');

$id_staf = $_SESSION['ID_staf'];
$id_permohonan = $_GET['id'] ?? null;

if (!$id_permohonan) {
    echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
    exit;
}

// Security check and get request header
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

// Get items for this request
$items_in_request = [];
$stmt_items = $conn->prepare("SELECT pb.no_kod, pb.kuantiti_mohon, prod.nama_produk AS perihal_stok
                            FROM permohonan_barang pb
                            JOIN PRODUK prod ON pb.no_kod = prod.ID_produk
                            WHERE pb.ID_permohonan = ?");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
while ($row = $result_items->fetch_assoc()) {
    $items_in_request[] = $row;
}
$stmt_items->close();

// Return JSON response
echo json_encode([
    'success' => true,
    'header' => $request_header,
    'items' => $items_in_request
]);

$conn->close();
exit;
?>