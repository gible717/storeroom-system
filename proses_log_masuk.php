<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$ID_staf = trim($_POST['ID_staf'] ?? '');
$katalaluan = $_POST['katalaluan'] ?? '';

if ($ID_staf === '' || $katalaluan === '') {
    header('Location: login.php?error=' . urlencode('Sila isi kedua-dua ruangan.'));
    exit;
}

$stmt = $conn->prepare('SELECT ID_staf, nama, katalaluan, peranan, is_first_login FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();

    if (password_verify($katalaluan, $row['katalaluan'])) {
        session_regenerate_id(true);
        $_SESSION['ID_staf'] = $row['ID_staf'];
        $_SESSION['nama'] = $row['nama'];
        $_SESSION['peranan'] = $row['peranan'];

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
    }
}

header('Location: login.php?error=' . urlencode('ID Staf atau Katalaluan salah.'));
exit;
?>
