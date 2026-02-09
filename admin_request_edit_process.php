<?php
// admin_request_edit_process.php - Handle admin request edit (AJAX)

require 'admin_auth_check.php';
require_once 'csrf.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Ralat tidak diketahui.'];

// Check POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['message'] = 'Kaedah penghantaran tidak sah.';
    echo json_encode($response);
    exit;
}

// Validate CSRF token
if (!csrf_validate()) {
    $response['message'] = 'Sesi anda telah tamat. Sila muat semula halaman.';
    echo json_encode($response);
    exit;
}

// Get form data
$id_permohonan = $_POST['id_permohonan'] ?? null;
$items = $_POST['items'] ?? [];
$current_user_id = $_SESSION['ID_staf'];

// Validate data
if (!$id_permohonan) {
    $response['message'] = 'ID Permohonan tidak sah.';
    echo json_encode($response);
    exit;
}
if (empty($items)) {
    $response['message'] = 'Permohonan mesti mempunyai sekurang-kurangnya satu item.';
    echo json_encode($response);
    exit;
}

// Verify request exists, is still 'Baru' status, AND belongs to current admin
$stmt = $conn->prepare("SELECT ID_permohonan FROM permohonan WHERE ID_permohonan = ? AND status = 'Baru' AND ID_pemohon = ?");
$stmt->bind_param("is", $id_permohonan, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) {
    $response['message'] = 'Permohonan tidak dapat dikemaskini (mungkin telah diluluskan, ditolak, atau anda tiada kebenaran).';
    echo json_encode($response);
    exit;
}
$stmt->close();

// Update request with transaction
$conn->begin_transaction();
try {
    // Delete old items
    $stmt_delete = $conn->prepare("DELETE FROM permohonan_barang WHERE ID_permohonan = ?");
    $stmt_delete->bind_param("i", $id_permohonan);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Insert new items
    $sql_insert = "INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);

    foreach ($items as $item) {
        $no_kod = $item['no_kod'];
        $kuantiti = (int)$item['kuantiti'];
        if ($kuantiti > 0) {
            $stmt_insert->bind_param("isi", $id_permohonan, $no_kod, $kuantiti);
            $stmt_insert->execute();
        }
    }
    $stmt_insert->close();

    // Commit transaction
    $conn->commit();
    $response['success'] = true;
    $response['message'] = "Permohonan #$id_permohonan telah berjaya dikemaskini.";

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = safeError("Gagal mengemaskini permohonan.", $e->getMessage());
}

echo json_encode($response);
$conn->close();
exit;
?>
