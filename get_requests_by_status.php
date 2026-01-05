<?php
// get_requests_by_status.php - API to fetch requests by status for staff dashboard modal

session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    echo json_encode(['success' => false, 'message' => 'Sila log masuk terlebih dahulu.']);
    exit;
}

$staffID = $_SESSION['ID_staf'];
$status = $_GET['status'] ?? '';

// Validate status parameter
if (empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak sah.']);
    exit;
}

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Helper function for Malay date formatting
function format_malay_date($tarikh_mohon, $masa_mohon) {
    $malay_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $today = date('Y-m-d');

    // For recent dates (today), show time
    if ($tarikh_mohon == $today && $masa_mohon && $masa_mohon != '0000-00-00 00:00:00') {
        $timestamp = strtotime($masa_mohon);
        $diff = time() - $timestamp;

        if ($diff < 86400 && $diff > 0) {
            if ($diff < 60) return "sebentar tadi";
            elseif ($diff < 3600) return round($diff / 60) . " minit yang lalu";
            else return round($diff / 3600) . " jam yang lalu";
        }
    }

    // Otherwise show full date
    $date = strtotime($tarikh_mohon);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}

// Build query based on status
if ($status === 'Semua') {
    // Get all requests
    $sql = "SELECT ID_permohonan, tarikh_mohon, masa_mohon, status
            FROM permohonan
            WHERE ID_pemohon = ?
            ORDER BY tarikh_mohon DESC, masa_mohon DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $staffID);
} else {
    // Get requests by specific status
    $sql = "SELECT ID_permohonan, tarikh_mohon, masa_mohon, status
            FROM permohonan
            WHERE ID_pemohon = ? AND status = ?
            ORDER BY tarikh_mohon DESC, masa_mohon DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $staffID, $status);
}

$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = [
        'ID_permohonan' => $row['ID_permohonan'],
        'tarikh_mohon' => $row['tarikh_mohon'],
        'masa_mohon' => $row['masa_mohon'],
        'status' => $row['status'],
        'tarikh_display' => format_malay_date($row['tarikh_mohon'], $row['masa_mohon'])
    ];
}

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    'success' => true,
    'requests' => $requests,
    'count' => count($requests)
]);
?>
