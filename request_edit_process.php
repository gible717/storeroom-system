<?php
// FILE: request_edit_process.php (NEW - v4.0 AJAX Version)
require 'staff_auth_check.php';

// Set header to JSON for AJAX responses
header('Content-Type: application/json');

// Start with a default error response
$response = ['success' => false, 'message' => 'Ralat tidak diketahui.'];

// 1. Get Data from POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['message'] = 'Kaedah penghantaran tidak sah.';
    echo json_encode($response);
    exit;
}

$id_permohonan = $_POST['id_permohonan'] ?? null;
$id_staf = $_SESSION['ID_staf'];
$items = $_POST['items'] ?? [];
$jawatan = $_POST['jawatan'] ?? '';
$catatan = $_POST['catatan'] ?? null;

// 2. Validation
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

// 3. Security Check: Verify user still owns this request and it's still 'Baru'
$stmt = $conn->prepare("SELECT ID_permohonan FROM permohonan 
                        WHERE ID_permohonan = ? AND ID_pemohon = ? AND status = 'Baru'");
$stmt->bind_param("is", $id_permohonan, $id_staf);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) {
    $response['message'] = 'Permohonan tidak dapat dikemaskini (mungkin telah diluluskan).';
    echo json_encode($response);
    exit;
}
$stmt->close();

// 4. Database Transaction
$conn->begin_transaction();
try {
    // 4a. Update the main 'permohonan' header
    $stmt_header = $conn->prepare("UPDATE permohonan SET jawatan_pemohon = ?, catatan = ? 
                                    WHERE ID_permohonan = ?");
    $stmt_header->bind_param("ssi", $jawatan, $catatan, $id_permohonan);
    $stmt_header->execute();
    $stmt_header->close();

    // 4b. Delete ALL old items from 'permohonan_barang'
    $stmt_delete = $conn->prepare("DELETE FROM permohonan_barang WHERE ID_permohonan = ?");
    $stmt_delete->bind_param("i", $id_permohonan);
    $stmt_delete->execute();
    $stmt_delete->close();
    
    // 4c. Insert ALL new items from the form
    $sql_insert = "INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) 
                VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    foreach ($items as $item) {
        $no_kod = $item['no_kod'];
        $kuantiti = (int)$item['kuantiti'];
        if ($kuantiti > 0) {
            $stmt_insert->bind_param("iii", $id_permohonan, $no_kod, $kuantiti);
            $stmt_insert->execute();
        }
    }
    $stmt_insert->close();

    // 4d. If everything is OK, commit
    $conn->commit();
    
    // --- Send SUCCESS JSON Response ---
    $response['success'] = true;
    $response['message'] = "Permohonan anda telah berjaya dikemaskini.";

} catch (Exception $e) {
    // 4e. If anything fails, roll back
    $conn->rollback();
    // --- Send ERROR JSON Response ---
    $response['message'] = "Gagal mengemaskini permohonan. Ralat: " . $e->getMessage();
}

// 5. Echo final JSON response
echo json_encode($response);
exit;
?>