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

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: admin_category.php?success=Kategori berjaya ditambah!");
            exit;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            if ($e->getCode() == 1062) {
                $_SESSION['form_data'] = ['nama_kategori' => $nama_kategori];
                $_SESSION['error_field'] = 'nama_kategori';
                header("Location: admin_category.php?error=" . urlencode("Kategori '$nama_kategori' sudah wujud dalam sistem. Sila gunakan nama yang lain."));
            } else {
                header("Location: admin_category.php?error=" . urlencode("Ralat pangkalan data: " . $e->getMessage()));
            }
            exit;
        }
    }

    // Edit/Update category
    else if ($action == 'edit' && !empty($_POST['ID_kategori']) && !empty($_POST['nama_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $nama_kategori = trim($_POST['nama_kategori']);

        $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE ID_kategori = ?");
        $stmt->bind_param("si", $nama_kategori, $ID_kategori);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: admin_category.php?success=Kategori berjaya dikemaskini!");
            exit;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            if ($e->getCode() == 1062) {
                $_SESSION['form_data'] = ['nama_kategori' => $nama_kategori, 'ID_kategori' => $ID_kategori];
                $_SESSION['error_field'] = 'nama_kategori';
                $_SESSION['edit_mode'] = true;
                header("Location: admin_category.php?error=" . urlencode("Kategori '$nama_kategori' sudah wujud dalam sistem. Sila gunakan nama yang lain."));
            } else {
                header("Location: admin_category.php?error=" . urlencode("Ralat pangkalan data: " . $e->getMessage()));
            }
            exit;
        }
    }

    // Delete category
    else if ($action == 'delete' && !empty($_POST['ID_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $stmt = $conn->prepare("DELETE FROM kategori WHERE ID_kategori = ?");
        $stmt->bind_param("i", $ID_kategori);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: admin_category.php?success=Kategori berjaya dipadam!");
            exit;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            if ($e->getCode() == 1451) {
                header("Location: admin_category.php?error=" . urlencode("Tidak boleh padam. Kategori ini sedang digunakan oleh produk."));
            } else {
                header("Location: admin_category.php?error=" . urlencode("Ralat pangkalan data: " . $e->getMessage()));
            }
            exit;
        }
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
