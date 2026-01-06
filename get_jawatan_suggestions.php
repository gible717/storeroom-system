<?php
// get_jawatan_suggestions.php - Get jawatan suggestions for autocomplete

require 'staff_auth_check.php';

header('Content-Type: application/json');

$id_staf = $_SESSION['ID_staf'];

// Get jawatan suggestions for this user
$suggestions = [];

// 1. Get jawatan from user's profile
$stmt_profile = $conn->prepare("SELECT jawatan FROM staf WHERE ID_staf = ? AND jawatan IS NOT NULL AND jawatan != ''");
$stmt_profile->bind_param("s", $id_staf);
$stmt_profile->execute();
$profile_result = $stmt_profile->get_result();
if ($row = $profile_result->fetch_assoc()) {
    $suggestions[] = [
        'value' => $row['jawatan'],
        'source' => 'profile',
        'label' => $row['jawatan'] . ' (Profil Anda)'
    ];
}
$stmt_profile->close();

// 2. Get distinct jawatan values from user's previous requests (up to 5 most recent)
$stmt_history = $conn->prepare("SELECT DISTINCT jawatan_pemohon
                                FROM permohonan
                                WHERE ID_pemohon = ?
                                AND jawatan_pemohon IS NOT NULL
                                AND jawatan_pemohon != ''
                                ORDER BY ID_permohonan DESC
                                LIMIT 5");
$stmt_history->bind_param("s", $id_staf);
$stmt_history->execute();
$history_result = $stmt_history->get_result();
while ($row = $history_result->fetch_assoc()) {
    // Don't add duplicate if it's same as profile jawatan
    $isDuplicate = false;
    foreach ($suggestions as $existing) {
        if ($existing['value'] === $row['jawatan_pemohon']) {
            $isDuplicate = true;
            break;
        }
    }

    if (!$isDuplicate) {
        $suggestions[] = [
            'value' => $row['jawatan_pemohon'],
            'source' => 'history',
            'label' => $row['jawatan_pemohon'] . ' (Permohonan Lepas)'
        ];
    }
}
$stmt_history->close();

echo json_encode([
    'success' => true,
    'suggestions' => $suggestions
]);

$conn->close();
exit;
?>
