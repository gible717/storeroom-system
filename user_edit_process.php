<?php
// user_edit_process.php - Handle user edit form

require 'admin_auth_check.php';
require_once 'csrf.php';

// Check POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_users.php");
    exit;
}

// Validate CSRF token
csrf_check('admin_users.php');

// Get form data
$id_staf = $_POST['id_staf'];
$nama = $_POST['nama'];
$emel = $_POST['emel'];
$id_jabatan = $_POST['id_jabatan'];
$is_admin = $_POST['is_admin'];

// Validate required fields
if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || !isset($is_admin)) {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Sila isi semua medan."));
    exit;
}

// Check duplicate email
$stmt = $conn->prepare("SELECT ID_staf FROM staf WHERE emel = ? AND ID_staf != ?");
$stmt->bind_param("ss", $emel, $id_staf);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Emel ini telah digunakan oleh pengguna lain."));
    exit;
}
$stmt->close();

// Update user
$sql = "UPDATE staf SET
            nama = ?,
            emel = ?,
            ID_jabatan = ?,
            is_admin = ?
        WHERE ID_staf = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiis", $nama, $emel, $id_jabatan, $is_admin, $id_staf);

if ($stmt->execute()) {
    // Update session if user updates themselves
    if ($id_staf == $_SESSION['ID_staf']) {
        $_SESSION['nama'] = $nama;
    }
    header("Location: admin_users.php?success=" . urlencode("Maklumat pengguna berjaya dikemaskini."));
} else {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Gagal mengemaskini data: " . $stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>