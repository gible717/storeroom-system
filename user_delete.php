<?php
// user_delete.php - Handle user deletion

require 'db.php';
session_start();

// Check admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php?error=Akses ditolak.");
    exit;
}

// Check ID
if (!isset($_GET['id'])) {
    header("Location: admin_users.php?error=Tiada ID pengguna diberikan");
    exit;
}

$id_staf_to_delete = $_GET['id'];
$current_admin_id = $_SESSION['ID_staf'];

// Prevent self-deletion
if ($id_staf_to_delete == $current_admin_id) {
    header("Location: admin_users.php?error=Anda tidak boleh memadam akaun anda sendiri!");
    exit;
}

// Delete user
$sql = "DELETE FROM staf WHERE ID_staf = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: admin_users.php?error=Ralat pangkalan data: " . $conn->error);
    exit;
}

$stmt->bind_param("s", $id_staf_to_delete);

if ($stmt->execute()) {
    header("Location: admin_users.php?success=Pengguna berjaya dipadam!");
} else {
    header("Location: admin_users.php?error=Gagal memadam pengguna: " . $stmt->error);
}

$stmt->close();
$conn->close();
exit;
?>