<?php
// admin_category_process.php - Handle category add/delete

require_once 'db.php';
require_once 'admin_auth_check.php';
// admin_auth_check.php already verifies admin access, no need to check again

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    $action = $_POST['action'];

    // Add category
    if ($action == 'add' && !empty($_POST['nama_kategori'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama_kategori);

        if ($stmt->execute()) {
            header("Location: admin_category.php?success=Kategori berjaya ditambah!");
            exit;
        } else {
            if ($conn->errno == 1062) {
                header("Location: admin_category.php?error=Kategori ini sudah wujud.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
            exit;
        }
        $stmt->close();
    }

    // Edit/Update category
    else if ($action == 'edit' && !empty($_POST['ID_kategori']) && !empty($_POST['nama_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $nama_kategori = trim($_POST['nama_kategori']);

        $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE ID_kategori = ?");
        $stmt->bind_param("si", $nama_kategori, $ID_kategori);

        if ($stmt->execute()) {
            header("Location: admin_category.php?success=Kategori berjaya dikemaskini!");
            exit;
        } else {
            if ($conn->errno == 1062) {
                header("Location: admin_category.php?error=Nama kategori ini sudah wujud.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
            exit;
        }
        $stmt->close();
    }

    // Delete category
    else if ($action == 'delete' && !empty($_POST['ID_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $stmt = $conn->prepare("DELETE FROM kategori WHERE ID_kategori = ?");
        $stmt->bind_param("i", $ID_kategori);

        if ($stmt->execute()) {
            header("Location: admin_category.php?success=Kategori berjaya dipadam!");
            exit;
        } else {
            if ($conn->errno == 1451) {
                header("Location: admin_category.php?error=Tidak boleh padam. Kategori ini sedang digunakan oleh produk.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
            exit;
        }
        $stmt->close();
    }

    else {
        header("Location: admin_category.php?error=Tindakan tidak sah.");
        exit;
    }

} else {
    header("Location: admin_category.php");
    exit;
}

$conn->close();
exit;
?>
