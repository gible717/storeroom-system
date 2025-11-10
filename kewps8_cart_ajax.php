<?php
// FILE: kewps8_cart_ajax.php
// This file handles the "Add to Cart" AJAX request.

session_start(); // We must start the session to access the $_SESSION 'cart'

// 1. Get the data from the AJAX (it's sent as JSON)
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $no_kod = $data['no_kod'];
    $kuantiti = (int)$data['kuantiti'];
    $perihal_stok = $data['perihal_stok'];
    $catatan = $data['catatan'];

    // 2. Initialize the cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // 3. Add or update the item in the cart
    // We use no_kod as the "key" to prevent duplicates and allow updates
    $_SESSION['cart'][$no_kod] = [
        'no_kod' => $no_kod,
        'kuantiti' => $kuantiti,
        'perihal_stok' => $perihal_stok
    ];
    
    // 4. Save the "catatan" (note) for the whole request
    // If the user types a new note, it just overwrites the old one
    if (!empty($catatan)) {
        $_SESSION['request_catatan'] = $catatan;
    }

    // 5. Send a success response back to the JavaScript
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart.',
        'cart_count' => count($_SESSION['cart']) // This updates the button counter
    ]);

} else {
    // No data was sent
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data.'
    ]);
}
?>