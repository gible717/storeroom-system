<?php
// FILE: login_process.php (FIXED, FINAL VERSION)
session_start();
require 'db.php';

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$ID_staf = trim($_POST['ID_staf'] ?? '');
$kata_laluan_dimasukkan = $_POST['kata_laluan'] ?? $_POST['katalaluan'] ?? '';

// Basic validation
if ($ID_staf === '' || $kata_laluan_dimasukkan === '') {
    header('Location: login.php?error=' . urlencode('Sila isi kedua-dua ruangan.'));
    exit;
}

// --- THIS IS THE FIX ---
// We select the NEW columns: is_admin, is_superadmin
$stmt = $conn->prepare('SELECT ID_staf, nama, katalaluan, is_first_login, is_admin, is_superadmin FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($kata_laluan_dimasukkan, $user['katalaluan'])) {
        // Password is correct, start session
        session_regenerate_id(true);
        
        // --- THIS IS THE FIX ---
        // We set the NEW session variables
        $_SESSION['ID_staf'] = $user['ID_staf'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_superadmin'] = $user['is_superadmin'];
        $_SESSION['is_first_login'] = $user['is_first_login'];

        // 1. Check for first-time login
        if ($user['is_first_login'] == 1) {
            header('Location: change_password.php');
            exit;
        }

        $msg = urlencode("Selamat datang kembali, " . $user['nama'] . "!");

        // --- THIS IS THE FIX ---
        // Redirect based on the NEW 'is_admin' column
        if ($user['is_admin'] == 1) {
            header('Location: admin_dashboard.php?success=' . $msg);
            exit;
        } else {
            header('Location: staff_dashboard.php?success=' . $msg);
            exit;
        }
    }
}

// If anything fails, redirect back to login
header('Location: login.php?error=' . urlencode('ID Staf atau Katalaluan salah.'));
exit;
?>