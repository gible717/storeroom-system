// check_table_structure.php - Debug transaksi_stok table structure
<?php
require 'db.php';

echo "<h2>transaksi_stok Table Structure</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

$result = $conn->query("DESCRIBE transaksi_stok");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Indexes</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Key Name</th><th>Column</th><th>Index Type</th></tr>";

$result = $conn->query("SHOW INDEX FROM transaksi_stok");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Key_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Column_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Index_type']) . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
