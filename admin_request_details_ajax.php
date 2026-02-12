<?php
// admin_request_details_ajax.php - Get request details for admin (AJAX)

require 'admin_auth_check.php';

header('Content-Type: application/json');

$id_permohonan = $_GET['id'] ?? null;

if (!$id_permohonan) {
    echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
    exit;
}

// Get request header with staff name
$stmt = $conn->prepare("SELECT p.*, s.nama AS nama_pemohon
                        FROM permohonan p
                        JOIN staf s ON p.ID_pemohon = s.ID_staf
                        WHERE p.ID_permohonan = ?");
$stmt->bind_param("i", $id_permohonan);
$stmt->execute();
$request_header = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request_header) {
    echo json_encode(['success' => false, 'message' => 'Permohonan tidak dijumpai.']);
    exit;
}

// Get items for this request
$items_in_request = [];
$stmt_items = $conn->prepare("SELECT pb.no_kod, pb.kuantiti_mohon, b.perihal_stok,
                                b.kategori,
                                CASE WHEN k.parent_id IS NOT NULL THEN k.nama_kategori ELSE NULL END AS subkategori
                            FROM permohonan_barang pb
                            JOIN barang b ON pb.no_kod = b.no_kod
                            LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
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
