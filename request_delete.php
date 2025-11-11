<?php
// FILE: request_delete.php (AJAX Version)
require 'staff_auth_check.php';

// Set header to JSON for AJAX responses
header('Content-Type: application/json');

// Start with a default error response
$response = ['success' => false, 'message' => 'Ralat tidak diketahui.'];

// 1. Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'ID Permohonan tidak sah.';
    echo json_encode($response);
    exit;
}

$id_permohonan = $_GET['id'];
$id_staf = $_SESSION['ID_staf'];

// 2. Security Check: Make sure this user owns this request and it's still 'Baru'
$stmt = $conn->prepare("SELECT ID_permohonan FROM permohonan 
                        WHERE ID_permohonan = ? AND ID_pemohon = ? AND status = 'Baru'");
$stmt->bind_param("is", $id_permohonan, $id_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Either not owned by user, or status is no longer 'Baru'
    $response['message'] = 'Permohonan tidak dapat dipadam (mungkin telah diluluskan atau bukan milik anda).';
    echo json_encode($response);
    exit;
}
$stmt->close();

// 3. Begin Transaction (We must delete from two tables)
$conn->begin_transaction();
try {
    // Delete from child table first (permohonan_barang)
    $stmt_items = $conn->prepare("DELETE FROM permohonan_barang WHERE ID_permohonan = ?");
    $stmt_items->bind_param("i", $id_permohonan);
    $stmt_items->execute();
    $stmt_items->close();

    // Delete from parent table (permohonan)
    $stmt_header = $conn->prepare("DELETE FROM permohonan WHERE ID_permohonan = ?");
    $stmt_header->bind_param("i", $id_permohonan);
    $stmt_header->execute();
    $stmt_header->close();

    // If both succeed, commit
    $conn->commit();
    
    // --- Send SUCCESS JSON Response ---
    $response['success'] = true;
    $response['message'] = "Permohonan telah berjaya dipadam.";

} catch (Exception $e) {
    // If anything fails, roll back
    $conn->rollback();
    // --- Send ERROR JSON Response ---
    $response['message'] = "Gagal memadam permohonan. Ralat: " . $e->getMessage();
}

// 4. Echo final JSON response
echo json_encode($response);
exit;
?>