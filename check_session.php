// check_session.php - Debug session and user data
<?php
session_start();
require 'db.php';

echo "<h2>Session Debug Info</h2>";
echo "<table border='1' cellpadding='5'>";

echo "<tr><th>Session Key</th><th>Value</th></tr>";

foreach ($_SESSION as $key => $value) {
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
    echo "<td>" . htmlspecialchars(print_r($value, true)) . "</td>";
    echo "</tr>";
}

echo "</table>";

if (isset($_SESSION['ID_staf'])) {
    echo "<h3>User Details from Database</h3>";
    $stmt = $conn->prepare("SELECT * FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $_SESSION['ID_staf']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    echo "<table border='1' cellpadding='5'>";
    foreach ($user as $key => $value) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>
