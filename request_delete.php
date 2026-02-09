<?php
// request_delete.php - Handle request deletion

require 'staff_auth_check.php';

// Check ID parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = 'ID Permohonan tidak sah.';
    header('Location: request_list.php');
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
    $_SESSION['error_msg'] = 'Permohonan tidak dapat dipadam (mungkin telah diluluskan atau bukan milik anda).';
    header('Location: request_list.php');
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
    $_SESSION['success_msg'] = "Permohonan #$id_permohonan telah berjaya dipadam.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_msg'] = safeError("Gagal memadam permohonan.", $e->getMessage());
}

header('Location: request_list.php');
exit;
?>