// check_barang_columns.php - Debug barang table structure
<?php
require 'db.php';

echo "<h2>barang Table Structure</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Column Name</th><th>Type</th></tr>";

$result = $conn->query("DESCRIBE barang");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "</tr>";
}
echo "</table>";
$conn->close();
?>
