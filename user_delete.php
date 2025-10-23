<?php
// FILE: user_delete.php
require 'db.php';
require 'auth_check.php';

// 1. Security: Only Admins can access this
if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php?error=Akses tidak dibenarkan");
    exit;
}

// 2. Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_users.php?error=Tiada ID pengguna diberikan");
    exit;
}

$id_staf_to_delete = $_GET['id'];
$current_admin_id = $_SESSION['user_id'];

// 3. CRITICAL: Prevent an Admin from deleting their own account
if ($id_staf_to_delete == $current_admin_id) {
    header("Location: admin_users.php?error=Anda tidak boleh memadam akaun anda sendiri!");
    exit;
}

// 4. Prepare and execute the delete statement
$sql = "DELETE FROM staf WHERE id_staf = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: admin_users.php?error=Ralat pangkalan data: " . $conn->error);
    exit;
}

$stmt->bind_param("s", $id_staf_to_delete);

// 5. Execute and send the AJAX pop-up message
if ($stmt->execute()) {
    // This is the AJAX redirect you wanted!
    header("Location: admin_users.php?success=Pengguna berjaya dipadam!");
} else {
    header("Location: admin_users.php?error=Gagal memadam pengguna: " . $stmt->error);
}

$stmt->close();
$conn->close();
exit;
?>