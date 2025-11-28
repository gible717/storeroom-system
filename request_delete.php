<?php
// request_delete.php - Handle request deletion (AJAX)

require 'staff_auth_check.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Ralat tidak diketahui.'];

// Check ID parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'ID Permohonan tidak sah.';
    echo json_encode($response);
    exit;
}

$id_permohonan = $_GET['id'];
$id_staf = $_SESSION['ID_staf'];

// Security check: verify ownership and status
$stmt = $conn->prepare("SELECT ID_permohonan FROM permohonan
                        WHERE ID_permohonan = ? AND ID_pemohon = ? AND status = 'Baru'");
$stmt->bind_param("is", $id_permohonan, $id_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    $response['message'] = 'Permohonan tidak dapat dipadam (mungkin telah diluluskan atau bukan milik anda).';
    echo json_encode($response);
    exit;
}
$stmt->close();

// Delete with transaction
$conn->begin_transaction();
try {
    // Delete items first
    $stmt_items = $conn->prepare("DELETE FROM permohonan_barang WHERE ID_permohonan = ?");
    $stmt_items->bind_param("i", $id_permohonan);
    $stmt_items->execute();
    $stmt_items->close();

    // Delete request header
    $stmt_header = $conn->prepare("DELETE FROM permohonan WHERE ID_permohonan = ?");
    $stmt_header->bind_param("i", $id_permohonan);
    $stmt_header->execute();
    $stmt_header->close();

    // Commit transaction
    $conn->commit();
    $response['success'] = true;
    $response['message'] = "Permohonan telah berjaya dipadam.";

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = "Gagal memadam permohonan. Ralat: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>