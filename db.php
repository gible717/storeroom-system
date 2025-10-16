<?php
// FILE: db.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "storeroom_db";

// THIS IS THE NEW, IMPORTANT LINE
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>