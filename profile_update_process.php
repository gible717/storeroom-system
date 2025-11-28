<?php
// profile_update_process.php - Handle profile update

session_start();

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    die("Sila log masuk untuk mengemaskini profil anda.");
}

require_once __DIR__ . '/db.php';

$user_id = $_SESSION['ID_staf'];
$user_role = $_SESSION['peranan'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $jawatan = $_POST['jawatan'];

    // Validate input
    if (empty($nama) || empty($emel) || !filter_var($emel, FILTER_VALIDATE_EMAIL)) {
        $error_msg = urlencode("Nama dan emel yang sah diperlukan.");
        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
        exit;
    }

    // Update database
    $stmt = $conn->prepare("UPDATE staf SET nama = ?, emel = ?, jawatan = ? WHERE ID_staf = ?");
    $stmt->bind_param("ssss", $nama, $emel, $jawatan, $user_id);

    if ($stmt->execute()) {
        $success_msg = urlencode("Profil anda telah berjaya dikemaskini.");
        $_SESSION['nama'] = $nama;

        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?success=" . $success_msg);
        } else {
            header("Location: staff_profile.php?success=" . $success_msg);
        }
    } else {
        $error_msg = urlencode("Gagal mengemaskini profil.");
        if ($user_role == 'Admin') {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
    }

    $stmt->close();
    $conn->close();
    exit;

} else {
    header('Location: ' . ($user_role == 'Admin' ? 'admin_dashboard.php' : 'staff_dashboard.php'));
    exit;
}
?>
