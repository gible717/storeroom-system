<?php
// profile_update_process.php - Handle profile update

session_start();

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    die("Sila log masuk untuk mengemaskini profil anda.");
}

require_once __DIR__ . '/db.php';

$user_id = $_SESSION['ID_staf'];
$is_admin = $_SESSION['is_admin'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $jawatan = $_POST['jawatan'];

    // Validate input
    if (empty($nama) || empty($emel) || !filter_var($emel, FILTER_VALIDATE_EMAIL)) {
        $error_msg = urlencode("Nama dan emel yang sah diperlukan.");
        if ($is_admin == 1) {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
        exit;
    }

    // Check if email is being changed to avoid duplicate error
    $current_email_check = $conn->prepare("SELECT emel FROM staf WHERE ID_staf = ?");
    $current_email_check->bind_param("s", $user_id);
    $current_email_check->execute();
    $current_email_result = $current_email_check->get_result();
    $current_email_row = $current_email_result->fetch_assoc();
    $current_email_check->close();

    // Update database
    $stmt = $conn->prepare("UPDATE staf SET nama = ?, emel = ?, jawatan = ? WHERE ID_staf = ?");
    $stmt->bind_param("ssss", $nama, $emel, $jawatan, $user_id);

    if ($stmt->execute()) {
        $success_msg = urlencode("Profil anda telah berjaya dikemaskini.");
        $_SESSION['nama'] = $nama;

        if ($is_admin == 1) {
            header("Location: admin_profile.php?success=" . $success_msg);
        } else {
            header("Location: staff_profile.php?success=" . $success_msg);
        }
    } else {
        $error_msg = urlencode("Gagal mengemaskini profil.");
        if ($is_admin == 1) {
            header("Location: admin_profile.php?error=" . $error_msg);
        } else {
            header("Location: staff_profile.php?error=" . $error_msg);
        }
    }

    $stmt->close();
    $conn->close();
    exit;

} else {
    header('Location: ' . ($is_admin == 1 ? 'admin_dashboard.php' : 'staff_dashboard.php'));
    exit;
}
?>
