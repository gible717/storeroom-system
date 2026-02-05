<?php
// admin_edit_product_process.php - Handles product update (returns JSON for AJAX)

session_start();
require_once 'admin_auth_check.php';

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

    if ($row && !empty($row['gambar_produk'])) {
        // Only delete file if no other product uses the same photo
        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM barang WHERE gambar_produk = ? AND no_kod != ?");
        $check->bind_param("ss", $row['gambar_produk'], $id_produk);
        $check->execute();
        $others = $check->get_result()->fetch_assoc()['cnt'];
        $check->close();

        if ($others == 0 && file_exists($row['gambar_produk'])) {
            unlink($row['gambar_produk']);
        }
    }

    $stmt = $conn->prepare("UPDATE barang SET gambar_produk = NULL WHERE no_kod = ?");
    $stmt->bind_param("s", $id_produk);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Foto telah dipadam.']);
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

// Get kategori name
$kategori_name = '';
$kategori_stmt = $conn->prepare("SELECT nama_kategori FROM KATEGORI WHERE ID_kategori = ?");
$kategori_stmt->bind_param("i", $ID_kategori);
$kategori_stmt->execute();
$kategori_result = $kategori_stmt->get_result();
if ($kategori_row = $kategori_result->fetch_assoc()) {
    $kategori_name = $kategori_row['nama_kategori'];
}
$kategori_stmt->close();

// Handle photo upload
$gambar_path = null;
$photo_uploaded = false;

if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['gambar_produk'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Format foto tidak sah. Sila gunakan JPG, PNG, atau WEBP.';
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
            unlink($old_row['gambar_produk']);
        }
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $id_produk . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $gambar_path = $destination;
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
    $response['message'] = 'Ralat kemaskini: ' . $e->getMessage();
}

$stmt->close();
$conn->close();

echo json_encode($response);
exit;
?>
