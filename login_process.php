<?php
// login_process.php - Handles login form submission

session_start();
require 'db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$ID_staf = trim($_POST['ID_staf'] ?? '');
$kata_laluan_dimasukkan = $_POST['kata_laluan'] ?? $_POST['katalaluan'] ?? '';

// Validate input
if ($ID_staf === '' || $kata_laluan_dimasukkan === '') {
    header('Location: login.php?error=' . urlencode('Sila isi kedua-dua ruangan.'));
    exit;
}

// Query user from database
$stmt = $conn->prepare('SELECT ID_staf, nama, kata_laluan, is_first_login, is_admin FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($kata_laluan_dimasukkan, $user['kata_laluan'])) {

        session_regenerate_id(true);

        // Set session variables
        $_SESSION['ID_staf'] = $user['ID_staf'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_first_login'] = $user['is_first_login'];

        // Force password change on first login
        if ($user['is_first_login'] == 1) {
            header('Location: change_password.php');
            exit;
        }

        $msg = urlencode("Selamat datang kembali, " . $user['nama'] . "!");

        // Redirect based on role
        if ($user['is_admin'] == 1) {
            header('Location: admin_dashboard.php?success=' . $msg);
            exit;
        } else {
            header('Location: staff_dashboard.php?success=' . $msg);
            exit;
        }
    }
}

// Login failed
header('Location: login.php?error=' . urlencode('ID Staf atau Katalaluan salah.'));
exit;
?>
