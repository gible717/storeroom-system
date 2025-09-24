<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$ID_staf = trim($_POST['ID_staf'] ?? '');
$katalaluan = $_POST['katalaluan'] ?? '';

echo "<pre>DEBUG login attempt: ID_staf=" . htmlspecialchars($ID_staf) . "\n";
echo "Entered password (plain): " . htmlspecialchars($katalaluan) . "\n</pre>";

if ($ID_staf === '' || $katalaluan === '') {
    header('Location: index.php?error=' . urlencode('Please enter both Staff ID and Password.'));
    exit;
}

$stmt = $conn->prepare('SELECT ID_staf, nama, katalaluan, peranan, is_first_login FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$res = $stmt->get_result();

if (! $res) {
    echo "DEBUG: query failed: " . $conn->error;
    exit;
}

if ($res->num_rows === 0) {
    echo "<p style='color:red;'>DEBUG: No user found with ID: " . htmlspecialchars($ID_staf) . "</p>";
    exit;
}

$row = $res->fetch_assoc();
echo "<pre>DEBUG row: "; print_r($row); echo "</pre>";

$verify = password_verify($katalaluan, $row['katalaluan']);
echo "<pre>DEBUG password_verify: "; var_export($verify); echo "\n</pre>";

if ($verify) {
    session_regenerate_id(true);
    $_SESSION['ID_staf'] = $row['ID_staf'];
    $_SESSION['nama'] = $row['nama'];
    $_SESSION['peranan'] = $row['peranan'];
    $_SESSION['is_first_login'] = $row['is_first_login'];

    echo "<pre>DEBUG session after login: "; print_r($_SESSION); echo "</pre>";

    if ($row['is_first_login'] == 1) {
        header('Location: ubah_katalaluan.php');
        exit;
    }

    if ($row['peranan'] === 'Admin') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_staf.php');
    }
    exit;
} else {
    echo "<p style='color:red;'>DEBUG: password did not verify.</p>";
    exit;
}

?>



