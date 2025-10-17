<?php
// FILE: db.php (Cleaned)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "storeroom_db";

// THIS IS THE LINE THAT PREVENTS SILENT ERRORS
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>