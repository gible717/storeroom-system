<?php
// kewps8_cart_ajax.php - Handle cart operations (AJAX)
session_start();

// Initialize cart & catatan if they don't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['request_catatan'])) {
    $_SESSION['request_catatan'] = '';
}

// Get data from AJAX request (JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

$action = $data['action'];
$response = ['success' => false];

// Handle the requested action
switch ($action) {

    // Add item to cart
    case 'add':
        $no_kod = $data['no_kod'] ?? null;
        $kuantiti = (int)($data['kuantiti'] ?? 0);
        $perihal_stok = $data['perihal_stok'] ?? 'Unknown';
        $stok_semasa = (int)($data['stok_semasa'] ?? 0);
        $catatan = $data['catatan'] ?? '';
        $jawatan = $data['jawatan'] ?? '';

        if ($kuantiti <= 0) {
            $response['message'] = 'Kuantiti mestilah 1 atau lebih.';
        } elseif (empty($no_kod)) {
            $response['message'] = 'Sila pilih barang.';
        } else {
            // Add or update the item in the cart
            $_SESSION['cart'][$no_kod] = [
                'no_kod' => $no_kod,
                'kuantiti' => $kuantiti,
                'perihal_stok' => $perihal_stok,
                'stok_semasa' => $stok_semasa
            ];
            $_SESSION['request_catatan'] = $catatan;
            $_SESSION['request_jawatan'] = $jawatan;

            $response['success'] = true;
            $response['message'] = 'Item berjaya ditambah!';
        }
        break;

    // Get cart contents
    case 'get':
        $response['success'] = true;
        $response['cart'] = $_SESSION['cart'];
        $response['catatan'] = $_SESSION['request_catatan'];
        $response['jawatan'] = $_SESSION['request_jawatan'] ?? '';
        break;

    // Get cart item count
    case 'get_count':
        $response['success'] = true;
        $response['count'] = count($_SESSION['cart']);
        break;

    // Update item quantity
    case 'update':
        $no_kod = $data['no_kod'] ?? null;
        $kuantiti = (int)($data['kuantiti'] ?? 0);

        if ($kuantiti > 0 && isset($_SESSION['cart'][$no_kod])) {
            $_SESSION['cart'][$no_kod]['kuantiti'] = $kuantiti;
            $response['success'] = true;
        } else {
            $response['message'] = 'Gagal kemaskini kuantiti.';
        }
        break;

    // Delete item from cart
    case 'delete':
        $no_kod = $data['no_kod'] ?? null;

        if (isset($_SESSION['cart'][$no_kod])) {
            unset($_SESSION['cart'][$no_kod]);
            $response['success'] = true;
        } else {
            $response['message'] = 'Gagal memadam item.';
        }
        break;

    // Clear entire cart
    case 'clear':
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Senarai telah dikosongkan.';
        break;

    default:
        $response['message'] = 'Tindakan tidak sah.';
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>