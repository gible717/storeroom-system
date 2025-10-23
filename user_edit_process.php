<?php
// FILE: user_edit_process.php
require 'admin_auth_check.php';

// 1. Check if data is POSTed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_users.php");
    exit;
}

// 2. Get all form data
$id_staf = $_POST['id_staf'];
$nama = $_POST['nama'];
$emel = $_POST['emel'];
$id_jabatan = $_POST['id_jabatan'];
$peranan = $_POST['peranan'];

// 3. Validation
if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || empty($peranan)) {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Sila isi semua medan."));
    exit;
}

// 4. Check for duplicate email (but ignore our own email)
$stmt = $conn->prepare("SELECT ID_staf FROM staf WHERE emel = ? AND ID_staf != ?");
$stmt->bind_param("ss", $emel, $id_staf);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Emel ini telah digunakan oleh pengguna lain."));
    exit;
}
$stmt->close();


// 5. Prepare the UPDATE statement
$sql = "UPDATE staf SET 
            nama = ?, 
            emel = ?, 
            ID_jabatan = ?, 
            peranan = ? 
        WHERE ID_staf = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiss", $nama, $emel, $id_jabatan, $peranan, $id_staf);

// 6. Execute and redirect with AJAX pop-up
if ($stmt->execute()) {
    header("Location: admin_users.php?success=" . urlencode("Maklumat pengguna berjaya dikemaskini."));
} else {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Gagal mengemaskini data: " . $stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>