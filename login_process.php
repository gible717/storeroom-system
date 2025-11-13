<?php
// FILE: login_process.php (FINAL, 100% CORRECTED VERSION)
session_start();
require 'db.php';

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$ID_staf = trim($_POST['ID_staf'] ?? '');
// This line correctly checks for 'katalaluan' from your HTML form
$kata_laluan_dimasukkan = $_POST['kata_laluan'] ?? $_POST['katalaluan'] ?? '';

// Basic validation
if ($ID_staf === '' || $kata_laluan_dimasukkan === '') {
    header('Location: login.php?error=' . urlencode('Sila isi kedua-dua ruangan.'));
    exit;
}

// Select the correct columns from the database
$stmt = $conn->prepare('SELECT ID_staf, nama, kata_laluan, is_first_login, is_admin, is_superadmin FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // This will now pass, because the hash in the DB is correct
    if (password_verify($kata_laluan_dimasukkan, $user['kata_laluan'])) {
        
        session_regenerate_id(true);
        
        // --- THIS IS THE FIX ---
        // We set the NEW session variables
        $_SESSION['ID_staf'] = $user['ID_staf'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_superadmin'] = $user['is_superadmin'];
        $_SESSION['is_first_login'] = $user['is_first_login'];

        // Check for first-time login
        if ($user['is_first_login'] == 1) {
            header('Location: change_password.php');
            exit;
        }

        $msg = urlencode("Selamat datang kembali, " . $user['nama'] . "!");

        // Redirect based on 'is_admin'
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