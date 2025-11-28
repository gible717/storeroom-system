<?php
// admin_category_process.php - Handle category add/delete

session_start();
require_once 'db.php';
require 'admin_header.php';

// Check admin access
$userRole = isset($_SESSION['userRole']) ? $_SESSION['userRole'] : null;
if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php?error=Akses tidak dibenarkan.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    $action = $_POST['action'];

    // Add category
    if ($action == 'add' && !empty($_POST['nama_kategori'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $stmt = $conn->prepare("INSERT INTO KATEGORI (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama_kategori);

        if ($stmt->execute()) {
            header("Location: admin_category.php?success=Kategori berjaya ditambah!");
        } else {
            if ($conn->errno == 1062) {
                header("Location: admin_category.php?error=Kategori ini sudah wujud.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
        }
        $stmt->close();
    }

    // Delete category
    else if ($action == 'delete' && !empty($_POST['ID_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $stmt = $conn->prepare("DELETE FROM KATEGORI WHERE ID_kategori = ?");
        $stmt->bind_param("i", $ID_kategori);

        if ($stmt->execute()) {
            header("Location: admin_category.php?success=Kategori berjaya dipadam!");
        } else {
            if ($conn->errno == 1451) {
                header("Location: admin_category.php?error=Tidak boleh padam. Kategori ini sedang digunakan oleh produk.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
        }
        $stmt->close();
    }

    else {
        header("Location: admin_category.php?error=Tindakan tidak sah.");
    }

} else {
    header("Location: admin_category.php");
}

$conn->close();
exit;
?>
