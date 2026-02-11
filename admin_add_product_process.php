<?php
// admin_add_product_process.php - Handles new product form submission (JSON response)

session_start();
require 'db.php';
require 'auth_check.php';
require_once 'image_optimizer.php';
require_once 'csrf.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Kaedah permintaan tidak sah.']);
    exit;
}

// Validate CSRF token
if (!csrf_validate()) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi anda telah tamat. Sila muat semula halaman.']);
    exit;
}

// Get form data
$id_produk = trim($_POST['id_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? '');
$ID_kategori = (int)($_POST['ID_kategori'] ?? 0);
$nama_pembekal = trim($_POST['nama_pembekal'] ?? '');
$harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : null;
$stok_semasa = !empty($_POST['stok_semasa']) ? (int)$_POST['stok_semasa'] : 0;

// Validate required fields
if (empty($id_produk) || empty($nama_produk) || $ID_kategori <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID Produk, Nama Produk dan Kategori wajib diisi.']);
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
if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['gambar_produk'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Server-side MIME validation (prevents spoofing)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $real_mime = $finfo->file($file['tmp_name']);
    if (!in_array($real_mime, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Format foto tidak sah. Sila gunakan JPG, PNG, atau WEBP.']);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Sambungan fail tidak sah.']);
        exit;
    }

    $upload_dir = 'uploads/product_images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = $id_produk . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $gambar_path = optimizeProductImage($destination);
    }
}

// Insert into barang table
$sql = "INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, harga_seunit, nama_pembekal, baki_semasa, gambar_produk) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Ralat pangkalan data: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssisdsis", $id_produk, $nama_produk, $ID_kategori, $kategori_name, $harga, $nama_pembekal, $stok_semasa, $gambar_path);

try {
    $stmt->execute();
    // Apply same photo to other selected products
    $applied_count = 0;
    if ($gambar_path && !empty($_POST['apply_photo_to'])) {
        $apply_ids = json_decode($_POST['apply_photo_to'], true);
        if (is_array($apply_ids) && count($apply_ids) > 0) {
            $update_stmt = $conn->prepare("UPDATE barang SET gambar_produk = ? WHERE no_kod = ?");
            foreach ($apply_ids as $other_id) {
                $other_id = trim($other_id);
                if ($other_id === '') continue;
                $update_stmt->bind_param("ss", $gambar_path, $other_id);
                $update_stmt->execute();
                if ($update_stmt->affected_rows > 0) $applied_count++;
            }
            $update_stmt->close();
        }
    }

    $msg = "Produk '$nama_produk' berjaya ditambah!";
    if ($applied_count > 0) {
        $msg .= " Foto turut digunakan untuk $applied_count produk lain.";
    }

    echo json_encode([
        'status' => 'success',
        'message' => $msg,
        'redirectUrl' => 'admin_products.php'
    ]);
} catch (mysqli_sql_exception $e) {
    // Clean up uploaded file on failure
    if ($gambar_path && file_exists($gambar_path)) {
        @unlink($gambar_path);
    }

    if ($e->getCode() === 1062) {
        echo json_encode([
            'status' => 'error',
            'message' => "ID Produk '$id_produk' sudah wujud dalam sistem. Sila gunakan ID yang lain.",
            'errorField' => 'id_produk'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => safeError('Ralat semasa menyimpan produk.', $e->getMessage())
        ]);
    }
}

$stmt->close();
$conn->close();
exit;
?>
