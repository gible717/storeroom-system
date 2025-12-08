<?php
// login_process.php - Handles login form submission

session_start();
require 'db.php';

// Function to shorten Malaysian names
function getShortenedName($full_name) {
    $prefixes_to_remove = [
        'MUHAMMAD', 'MOHD', 'MUHD', 'MOHAMMAD', 'MOHAMAD',
        'SITI', 'NUR', 'KU', 'WAN', 'SYED', 'SHARIFAH',
        'TENGKU', 'RAJA', 'ANAK', 'NIK', 'CHE'
    ];

    $name_upper = strtoupper(trim($full_name));
    $bin_pos = stripos($name_upper, ' BIN ');
    $binti_pos = stripos($name_upper, ' BINTI ');

    if ($bin_pos !== false || $binti_pos !== false) {
        $split_pos = ($bin_pos !== false) ? $bin_pos : $binti_pos;
        $name_upper = trim(substr($name_upper, 0, $split_pos));
    }

    $parts = explode(' ', $name_upper);
    $filtered = [];
    foreach ($parts as $part) {
        if (!in_array($part, $prefixes_to_remove)) {
            $filtered[] = $part;
        }
    }

    if (count($filtered) > 0) {
        return implode(' ', $filtered);
    } else {
        return $parts[0];
    }
}

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

        // Use shortened name for welcome message
        $shortName = getShortenedName($user['nama']);
        $msg = urlencode("Selamat datang kembali, " . $shortName . "!");

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
