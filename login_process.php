<?php
// FILE: login_process.php
session_start();
require 'db.php';

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$ID_staf = trim($_POST['ID_staf'] ?? '');
$katalaluan = $_POST['katalaluan'] ?? '';

// Basic validation
if ($ID_staf === '' || $katalaluan === '') {
    header('Location: login.php?error=' . urlencode('Sila isi kedua-dua ruangan.'));
    exit;
}

// Prepare and execute statement to find user
$stmt = $conn->prepare('SELECT ID_staf, nama, katalaluan, peranan, is_first_login FROM staf WHERE ID_staf = ? LIMIT 1');
$stmt->bind_param('s', $ID_staf);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($katalaluan, $user['katalaluan'])) {
        // Password is correct, start session
        session_regenerate_id(true);
        $_SESSION['ID_staf'] = $user['ID_staf'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['peranan'] = $user['peranan'];

        // UPDATED: Redirect for first login password change
        if ($user['is_first_login'] == 1) {
            header('Location: change_password.php');
            exit;
        }

        // --- AJAX POP-UP FIX: Add success message ---
        $msg = urlencode("Selamat datang kembali, " . $user['nama'] . "!");

        // Redirect based on role
        if ($user['peranan'] === 'Admin') {
            header('Location: admin_dashboard.php?success=' . $msg);
        } else {
            header('Location: staff_dashboard.php?success=' . $msg);
        }
        exit;
    }
}

// If anything fails, redirect back to login with a generic error
header('Location: login.php?error=' . urlencode('ID Staf atau Katalaluan salah.'));
exit;
?>