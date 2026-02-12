<?php
// admin_category_process.php - Handle category add/edit/delete with subcategory support

require_once 'db.php';
require_once 'admin_auth_check.php';
require_once 'csrf.php';

// Validate CSRF token
csrf_check('admin_category.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    $action = $_POST['action'];

    // Add category
    if ($action == 'add' && !empty($_POST['nama_kategori'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if ($parent_id !== null) {
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, parent_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nama_kategori, $parent_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, parent_id) VALUES (?, NULL)");
            $stmt->bind_param("s", $nama_kategori);
        }

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: admin_category.php?success=Kategori berjaya ditambah!");
            exit;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            if ($e->getCode() == 1062) {
                $_SESSION['form_data'] = ['nama_kategori' => $nama_kategori, 'parent_id' => $parent_id];
                $_SESSION['error_field'] = 'nama_kategori';
                header("Location: admin_category.php?error=" . urlencode("Kategori '$nama_kategori' sudah wujud dalam sistem. Sila gunakan nama yang lain."));
            } else {
                header("Location: admin_category.php?error=" . urlencode(safeError("Ralat pangkalan data.", $e->getMessage())));
            }
            exit;
        }
    }

    // Edit/Update category
    else if ($action == 'edit' && !empty($_POST['ID_kategori']) && !empty($_POST['nama_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $nama_kategori = trim($_POST['nama_kategori']);
        $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;

        // Validation: cannot set parent to self
        if ($parent_id !== null && $parent_id === $ID_kategori) {
            header("Location: admin_category.php?error=" . urlencode("Kategori tidak boleh menjadi subkategori kepada dirinya sendiri."));
            exit;
        }

        // Validation: cannot convert to subcategory if it has its own subcategories
        if ($parent_id !== null) {
            $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM KATEGORI WHERE parent_id = ?");
            $check->bind_param("i", $ID_kategori);
            $check->execute();
            $has_children = $check->get_result()->fetch_assoc()['cnt'] > 0;
            $check->close();

            if ($has_children) {
                header("Location: admin_category.php?error=" . urlencode("Tidak boleh tukar kepada subkategori kerana kategori ini mempunyai subkategori sendiri. Padam subkategori dahulu."));
                exit;
            }
        }

        // Perform the update
        if ($parent_id !== null) {
            $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, parent_id = ? WHERE ID_kategori = ?");
            $stmt->bind_param("sii", $nama_kategori, $parent_id, $ID_kategori);
        } else {
            $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, parent_id = NULL WHERE ID_kategori = ?");
            $stmt->bind_param("si", $nama_kategori, $ID_kategori);
        }

        try {
            $stmt->execute();
            $stmt->close();

            // Cascade update barang.kategori to keep denormalized field in sync
            if ($parent_id === null) {
                // Main category renamed: update all products under this main + its subcategories
                $sub_stmt = $conn->prepare("SELECT ID_kategori FROM KATEGORI WHERE parent_id = ?");
                $sub_stmt->bind_param("i", $ID_kategori);
                $sub_stmt->execute();
                $sub_result = $sub_stmt->get_result();
                $all_ids = [$ID_kategori];
                while ($sub = $sub_result->fetch_assoc()) {
                    $all_ids[] = $sub['ID_kategori'];
                }
                $sub_stmt->close();

                $placeholders = implode(',', array_fill(0, count($all_ids), '?'));
                $types = str_repeat('i', count($all_ids));
                $update_sql = "UPDATE barang SET kategori = ? WHERE ID_kategori IN ($placeholders)";
                $update_stmt = $conn->prepare($update_sql);
                $params = array_merge([$nama_kategori], $all_ids);
                $update_stmt->bind_param("s" . $types, ...$params);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Converted to subcategory: update products to use parent's main category name
                $parent_stmt = $conn->prepare("SELECT nama_kategori FROM KATEGORI WHERE ID_kategori = ?");
                $parent_stmt->bind_param("i", $parent_id);
                $parent_stmt->execute();
                $parent_name = $parent_stmt->get_result()->fetch_assoc()['nama_kategori'] ?? '';
                $parent_stmt->close();

                if ($parent_name !== '') {
                    $update_stmt = $conn->prepare("UPDATE barang SET kategori = ? WHERE ID_kategori = ?");
                    $update_stmt->bind_param("si", $parent_name, $ID_kategori);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }

            header("Location: admin_category.php?success=Kategori berjaya dikemaskini!");
            exit;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            if ($e->getCode() == 1062) {
                $_SESSION['form_data'] = ['nama_kategori' => $nama_kategori, 'ID_kategori' => $ID_kategori, 'parent_id' => $parent_id];
                $_SESSION['error_field'] = 'nama_kategori';
                $_SESSION['edit_mode'] = true;
                header("Location: admin_category.php?error=" . urlencode("Kategori '$nama_kategori' sudah wujud dalam sistem. Sila gunakan nama yang lain."));
            } else {
                header("Location: admin_category.php?error=" . urlencode(safeError("Ralat pangkalan data.", $e->getMessage())));
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
                // Check if blocked by subcategories or products
                $sub_check = $conn->prepare("SELECT COUNT(*) AS cnt FROM KATEGORI WHERE parent_id = ?");
                $sub_check->bind_param("i", $ID_kategori);
                $sub_check->execute();
                $has_subs = $sub_check->get_result()->fetch_assoc()['cnt'] > 0;
                $sub_check->close();

                if ($has_subs) {
                    header("Location: admin_category.php?error=" . urlencode("Tidak boleh padam. Kategori ini mempunyai subkategori. Padam subkategori dahulu."));
                } else {
                    header("Location: admin_category.php?error=" . urlencode("Tidak boleh padam. Kategori ini sedang digunakan oleh produk."));
                }
            } else {
                header("Location: admin_category.php?error=" . urlencode(safeError("Ralat pangkalan data.", $e->getMessage())));
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
