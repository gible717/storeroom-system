<?php
// admin_edit_product_process.php - Handles product update (returns JSON for AJAX)

session_start();
require_once 'admin_auth_check.php';
require_once 'image_optimizer.php';
require_once 'csrf.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Ralat tidak diketahui.'];

// Check admin access
if ($isAdmin != 1) {
    $response['message'] = 'Akses tidak dibenarkan.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = 'Kaedah tidak sah.';
    echo json_encode($response);
    exit;
}

// Validate CSRF token
if (!csrf_validate()) {
    $response['message'] = 'Sesi anda telah tamat. Sila muat semula halaman.';
    echo json_encode($response);
    exit;
}

$id_produk = trim($_POST['id_produk'] ?? '');

if (empty($id_produk)) {
    $response['message'] = 'ID Produk tidak sah.';
    echo json_encode($response);
    exit;
}

// --- Handle photo delete request ---
if (isset($_POST['delete_photo'])) {
    $stmt = $conn->prepare("SELECT gambar_produk FROM barang WHERE no_kod = ?");
    $stmt->bind_param("s", $id_produk);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $photo_path = $row['gambar_produk'] ?? null;

    // Get list of other products to also remove photo from (if any)
    $also_remove_from = [];
    if (!empty($_POST['also_remove_from'])) {
        $also_remove_from = json_decode($_POST['also_remove_from'], true) ?: [];
    }

    // Build list of all product IDs to remove photo from
    $all_ids_to_update = [$id_produk];
    foreach ($also_remove_from as $other_id) {
        $all_ids_to_update[] = trim($other_id);
    }

    // Remove photo reference from all selected products
    $placeholders = implode(',', array_fill(0, count($all_ids_to_update), '?'));
    $types = str_repeat('s', count($all_ids_to_update));
    $update_sql = "UPDATE barang SET gambar_produk = NULL WHERE no_kod IN ($placeholders)";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param($types, ...$all_ids_to_update);
    $update_stmt->execute();
    $update_stmt->close();

    // Check if any other product still uses this photo
    if ($photo_path && file_exists($photo_path)) {
        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM barang WHERE gambar_produk = ?");
        $check->bind_param("s", $photo_path);
        $check->execute();
        $remaining = $check->get_result()->fetch_assoc()['cnt'];
        $check->close();

        // Only delete file if no product uses it anymore
        if ($remaining == 0) {
            if (!@unlink($photo_path)) {
                error_log("[STOREROOM] Failed to delete product photo: $photo_path");
            }
        }
    }

    $count = count($all_ids_to_update);
    $msg = $count > 1 ? "Foto telah dipadam daripada $count produk." : 'Foto telah dipadam.';
    echo json_encode(['status' => 'success', 'message' => $msg]);
    $conn->close();
    exit;
}

// --- Handle product update ---
$nama_produk = trim($_POST['nama_produk'] ?? '');
$ID_kategori = (int)($_POST['ID_kategori'] ?? 0);
$nama_pembekal = trim($_POST['nama_pembekal'] ?? '');
$harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
$stok_semasa = (int)($_POST['stok_semasa'] ?? 0);

if (empty($nama_produk)) {
    $response['message'] = 'Nama Produk wajib diisi.';
    echo json_encode($response);
    exit;
}
if ($ID_kategori <= 0) {
    $response['message'] = 'Kategori wajib dipilih.';
    echo json_encode($response);
    exit;
}

// Get kategori name - resolve to MAIN category name for denormalized field
$kategori_name = '';
$kategori_stmt = $conn->prepare("
    SELECT COALESCE(p.nama_kategori, k.nama_kategori) AS main_kategori_name
    FROM KATEGORI k
    LEFT JOIN KATEGORI p ON k.parent_id = p.ID_kategori
    WHERE k.ID_kategori = ?
");
$kategori_stmt->bind_param("i", $ID_kategori);
$kategori_stmt->execute();
$kategori_result = $kategori_stmt->get_result();
if ($kategori_row = $kategori_result->fetch_assoc()) {
    $kategori_name = $kategori_row['main_kategori_name'];
}
$kategori_stmt->close();

// Handle photo upload
$gambar_path = null;
$photo_uploaded = false;

if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['gambar_produk'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Server-side MIME validation (prevents spoofing)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $real_mime = $finfo->file($file['tmp_name']);
    if (!in_array($real_mime, $allowed_types)) {
        $response['message'] = 'Format foto tidak sah. Sila gunakan JPG, PNG, atau WEBP.';
        echo json_encode($response);
        exit;
    }

    $ext_check = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext_check, $allowed_extensions)) {
        $response['message'] = 'Sambungan fail tidak sah.';
        echo json_encode($response);
        exit;
    }

    $upload_dir = 'uploads/product_images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Delete old photo file
    $old_stmt = $conn->prepare("SELECT gambar_produk FROM barang WHERE no_kod = ?");
    $old_stmt->bind_param("s", $id_produk);
    $old_stmt->execute();
    $old_row = $old_stmt->get_result()->fetch_assoc();
    $old_stmt->close();

    if ($old_row && !empty($old_row['gambar_produk'])) {
        // Only delete old file if no other product uses it
        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM barang WHERE gambar_produk = ? AND no_kod != ?");
        $check->bind_param("ss", $old_row['gambar_produk'], $id_produk);
        $check->execute();
        $others = $check->get_result()->fetch_assoc()['cnt'];
        $check->close();

        if ($others == 0 && file_exists($old_row['gambar_produk'])) {
            if (!@unlink($old_row['gambar_produk'])) {
                error_log("[STOREROOM] Failed to delete old product photo: " . $old_row['gambar_produk']);
            }
        }
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $id_produk . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $gambar_path = optimizeProductImage($destination);
        $photo_uploaded = true;
    }
}

// Build update query
if ($photo_uploaded) {
    $sql = "UPDATE barang SET perihal_stok = ?, ID_kategori = ?, kategori = ?, harga_seunit = ?, nama_pembekal = ?, baki_semasa = ?, gambar_produk = ? WHERE no_kod = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdsiss", $nama_produk, $ID_kategori, $kategori_name, $harga, $nama_pembekal, $stok_semasa, $gambar_path, $id_produk);
} else {
    $sql = "UPDATE barang SET perihal_stok = ?, ID_kategori = ?, kategori = ?, harga_seunit = ?, nama_pembekal = ?, baki_semasa = ? WHERE no_kod = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdsis", $nama_produk, $ID_kategori, $kategori_name, $harga, $nama_pembekal, $stok_semasa, $id_produk);
}

if ($stmt === false) {
    $response['message'] = 'Ralat pangkalan data: ' . $conn->error;
    echo json_encode($response);
    exit;
}

try {
    $stmt->execute();

    // Apply photo to other products if requested
    $applied_count = 0;
    if ($photo_uploaded && $gambar_path && !empty($_POST['apply_photo_to'])) {
        $apply_ids = json_decode($_POST['apply_photo_to'], true);
        if (is_array($apply_ids) && count($apply_ids) > 0) {
            $update_stmt = $conn->prepare("UPDATE barang SET gambar_produk = ? WHERE no_kod = ?");
            foreach ($apply_ids as $other_id) {
                $other_id = trim($other_id);
                if ($other_id === '' || $other_id === $id_produk) continue;
                $update_stmt->bind_param("ss", $gambar_path, $other_id);
                $update_stmt->execute();
                if ($update_stmt->affected_rows > 0) $applied_count++;
            }
            $update_stmt->close();
        }
    }

    $msg = 'Produk berjaya dikemaskini!';
    if ($applied_count > 0) {
        $msg .= " Foto turut digunakan untuk $applied_count produk lain.";
    }

    $response['status'] = 'success';
    $response['message'] = $msg;
    $response['redirectUrl'] = 'admin_products.php';
} catch (mysqli_sql_exception $e) {
    $response['message'] = safeError('Ralat semasa mengemaskini produk.', $e->getMessage());
}

$stmt->close();
$conn->close();

echo json_encode($response);
exit;
?>
