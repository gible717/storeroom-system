<?php
// FILE: user_edit_process.php (FIXED)
require 'admin_auth_check.php'; // This correctly includes db.php

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
$is_admin = $_POST['is_admin']; // This is the correct variable

// --- "STEAK" (FIX): VALIDATE THE CORRECT VARIABLE ---
// We "slayed" (killed) 'empty($peranan)' and added '!isset($is_admin)'
// We use !isset because '0' (for Staf) is a valid value, but empty(0) is true.
if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || !isset($is_admin)) {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Sila isi semua medan."));
    exit;
}
// ---------------- END OF FIX -------------------

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

// 5. Prepare the UPDATE statement (This was already correct)
$sql = "UPDATE staf SET 
            nama = ?, 
            emel = ?, 
            ID_jabatan = ?, 
            is_admin = ? 
        WHERE ID_staf = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiis", $nama, $emel, $id_jabatan, $is_admin, $id_staf);

// 6. Execute and redirect
if ($stmt->execute()) {
    
    // --- "STEAK" (FIX): UPDATE SESSION IF USER UPDATES THEMSELVES ---
    if ($id_staf == $_SESSION['ID_staf']) {
        $_SESSION['nama'] = $nama;
    }
    // -------------------- END OF FIX --------------------

    header("Location: admin_users.php?success=" . urlencode("Maklumat pengguna berjaya dikemaskini."));
} else {
    header("Location: user_edit.php?id=$id_staf&error=" . urlencode("Gagal mengemaskini data: " . $stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>