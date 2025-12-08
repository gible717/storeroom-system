<?php
// db.php - Database connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "storeroom_db";

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to format dates in Malay
function formatMalayDate($date, $format = 'short') {
    $months_short = ['Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mac', 'Apr' => 'Apr',
                     'May' => 'Mei', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ogos',
                     'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Dis'];

    $months_long = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Mac',
                    'April' => 'April', 'May' => 'Mei', 'June' => 'Jun',
                    'July' => 'Julai', 'August' => 'Ogos', 'September' => 'September',
                    'October' => 'Oktober', 'November' => 'November', 'December' => 'Disember'];

    if ($format === 'short') {
        // Format: 08 Dis 2025
        $formatted = date('d M Y', strtotime($date));
        return str_replace(array_keys($months_short), array_values($months_short), $formatted);
    } else {
        // Format: 08 Disember 2025
        $formatted = date('d F Y', strtotime($date));
        return str_replace(array_keys($months_long), array_values($months_long), $formatted);
    }
}
?>
