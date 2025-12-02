<?php
// debug_reports.php - Debug script to check report data

require_once 'db.php';
require_once 'admin_auth_check.php';

echo "<h2>Debug: Permohonan Status Breakdown</h2>";

// Get ALL permohonan records with their status
$all_records = $conn->query("SELECT ID_permohonan, tarikh_mohon, status FROM permohonan ORDER BY tarikh_mohon DESC LIMIT 50");

echo "<h3>Last 50 Permohonan Records:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Tarikh Mohon</th><th>Status</th></tr>";

$status_counts = ['Baru' => 0, 'Diluluskan' => 0, 'Ditolak' => 0, 'Diterima' => 0, 'Other' => 0];

while ($row = $all_records->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['ID_permohonan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['tarikh_mohon']) . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['status']) . "</strong></td>";
    echo "</tr>";

    // Count statuses
    if (isset($status_counts[$row['status']])) {
        $status_counts[$row['status']]++;
    } else {
        $status_counts['Other']++;
    }
}
echo "</table>";

echo "<hr>";
echo "<h3>Status Summary (All Time):</h3>";
echo "<ul>";
foreach ($status_counts as $status => $count) {
    echo "<li><strong>$status:</strong> $count</li>";
}
echo "</ul>";

// Check current month filter
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

echo "<hr>";
echo "<h3>Current Month Filter: $current_month_start to $current_month_end</h3>";

$month_query = "SELECT status, COUNT(*) as count FROM permohonan WHERE DATE(tarikh_mohon) BETWEEN ? AND ? GROUP BY status";
$stmt = $conn->prepare($month_query);
$stmt->bind_param("ss", $current_month_start, $current_month_end);
$stmt->execute();
$result = $stmt->get_result();

echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>" . htmlspecialchars($row['status']) . ":</strong> " . $row['count'] . "</li>";
}
echo "</ul>";

$conn->close();
?>
<br><br>
<a href="admin_reports.php">← Back to Reports</a>
