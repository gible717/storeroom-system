<?php
// FILE: kewps8_cart_ajax.php (VERSI 4.0 - "Modal" Design)
// This file is now a "smart" processor for the session cart
session_start();

// --- 1. Initialize cart & catatan if they don't exist ---
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['request_catatan'])) {
    $_SESSION['request_catatan'] = '';
}

// --- 2. Get the data from the AJAX (it's sent as JSON) ---
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

$action = $data['action'];
$response = ['success' => false]; // Start with a pessimistic response

// --- 3. Handle the requested action ---
switch ($action) {
    
    // --- ACTION: 'add' (from the "Tambah Item" button) ---
    case 'add':
        $no_kod = $data['no_kod'] ?? null;
        $kuantiti = (int)($data['kuantiti'] ?? 0);
        $perihal_stok = $data['perihal_stok'] ?? 'Unknown';
        $catatan = $data['catatan'] ?? '';
        $jawatan = $data['jawatan'] ?? ''; // <-- ADD THIS

        if ($kuantiti <= 0) {
            $response['message'] = 'Kuantiti mestilah 1 atau lebih.';
        } elseif (empty($no_kod)) {
            $response['message'] = 'Sila pilih barang.';
        } else {
            // Add or update the item in the cart
            $_SESSION['cart'][$no_kod] = [
                'no_kod' => $no_kod,
                'kuantiti' => $kuantiti,
                'perihal_stok' => $perihal_stok
            ];
            // Always update the catatan
            $_SESSION['request_catatan'] = $catatan;
            $_SESSION['request_jawatan'] = $jawatan; // <-- ADD THIS
            
            $response['success'] = true;
            $response['message'] = 'Item berjaya ditambah!';
        }
        break;

    // --- ACTION: 'get' (from the "Sahkan" modal opening) ---
    case 'get':
        $response['success'] = true;
        $response['cart'] = $_SESSION['cart'];
        $response['catatan'] = $_SESSION['request_catatan'];
        break;

    // --- ACTION: 'get_count' (for checking the "Sahkan" button) ---
    case 'get_count':
        $response['success'] = true;
        $response['count'] = count($_SESSION['cart']);
        break;

    // --- ACTION: 'update' (from the modal quantity change) ---
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

    // --- ACTION: 'delete' (from the modal trash icon) ---
    case 'delete':
        $no_kod = $data['no_kod'] ?? null;

        if (isset($_SESSION['cart'][$no_kod])) {
            unset($_SESSION['cart'][$no_kod]);
            $response['success'] = true;
        } else {
            $response['message'] = 'Gagal memadam item.';
        }
        break;

    default:
        $response['message'] = 'Tindakan tidak sah.';
}

// --- 4. Send the JSON response back to the JavaScript ---
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>