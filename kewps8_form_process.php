<?php
/**
 * KEW.PS-8 Form Processing (AJAX)
 *
 * PURPOSE:
 * Handles staff request submission for inventory items.
 * Creates request header in 'permohonan' table and detail lines in 'permohonan_barang'.
 *
 * WORKFLOW:
 * 1. Validate session and cart items
 * 2. Get staff details from database
 * 3. Begin database transaction
 * 4. Insert request header (status = 'Baru')
 * 5. Insert request items from cart
 * 6. Send Telegram notification to admin
 * 7. Commit transaction and clear cart
 *
 * INPUT: Cart items from $_SESSION['cart'], staff ID from session
 * OUTPUT: JSON response with success/error message and request ID
 *
 * TABLES AFFECTED:
 * - permohonan (INSERT new request)
 * - permohonan_barang (INSERT request items)
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json');

// Security checks
if (!isset($_SESSION['ID_staf'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi anda telah tamat. Sila log masuk semula.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Kaedah penghantaran tidak sah.']);
    exit;
}

// Validate CSRF token
if (!csrf_validate()) {
    echo json_encode(['success' => false, 'message' => 'Sesi anda telah tamat. Sila muat semula halaman.']);
    exit;
}

// Get data from session
$staff_id = $_SESSION['ID_staf'];
$items = $_SESSION['cart'] ?? [];
$catatan = $_SESSION['request_catatan'] ?? null;
$jawatan_pemohon_session = $_SESSION['request_jawatan'] ?? null;

// Validate cart
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Tiada item dalam senarai permohonan anda. Sila tambah item.']);
    exit;
}

// Get staff details
$stmt = $conn->prepare("SELECT nama, jawatan, ID_jabatan FROM staf WHERE ID_staf = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Ralat: Data staf tidak dijumpai.']);
    exit;
}

$nama_pemohon = $user['nama'];
$jawatan_pemohon = $jawatan_pemohon_session ?? ($user['jawatan'] ?? '');
$id_jabatan = !empty($user['ID_jabatan']) ? (int)$user['ID_jabatan'] : null;
$tarikh_mohon = date('Y-m-d'); // Current date

// --- 3. Database Transaction ---
$conn->begin_transaction();

try {
    // --- 4. Insert into `permohonan` (Header Table) ---
    // Handle NULL ID_jabatan for admin users who may not have a department
    if ($id_jabatan !== null) {
        $sql_header = "INSERT INTO permohonan
                    (tarikh_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan)
                    VALUES (?, 'Baru', ?, ?, ?, ?, ?)";
        $stmt_header = $conn->prepare($sql_header);
        $stmt_header->bind_param("ssssis",
            $tarikh_mohon,
            $staff_id,
            $nama_pemohon,
            $jawatan_pemohon,
            $id_jabatan,
            $catatan
        );
    } else {
        // If no department, insert NULL for ID_jabatan
        $sql_header = "INSERT INTO permohonan
                    (tarikh_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan)
                    VALUES (?, 'Baru', ?, ?, ?, NULL, ?)";
        $stmt_header = $conn->prepare($sql_header);
        $stmt_header->bind_param("sssss",
            $tarikh_mohon,
            $staff_id,
            $nama_pemohon,
            $jawatan_pemohon,
            $catatan
        );
    }

    $stmt_header->execute();

    // Get the ID of the new request we just created
    $id_permohonan_baru = $conn->insert_id;
    $stmt_header->close();

    // --- 5. Insert into `permohonan_barang` (Detail Table) ---
    $sql_items = "INSERT INTO permohonan_barang 
                (ID_permohonan, no_kod, kuantiti_mohon) 
                VALUES (?, ?, ?)";
    
    $stmt_items = $conn->prepare($sql_items);

    // Loop through each item from the SESSION cart
    foreach ($items as $item) {
        $no_kod = $item['no_kod'];
        $kuantiti_mohon = $item['kuantiti']; // In session, key is 'kuantiti'

        $stmt_items->bind_param("isi", $id_permohonan_baru, $no_kod, $kuantiti_mohon);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // --- 6. If everything is OK, commit the transaction ---
    $conn->commit();

    // --- 7. Send Telegram notification to admins ---
    require_once __DIR__ . '/telegram_helper.php';

    $item_count = count($items);

    // Send notification (non-blocking - won't affect user experience if it fails)
    send_new_request_notification(
        $id_permohonan_baru,
        $nama_pemohon,
        $jawatan_pemohon,
        $item_count,
        $catatan ?? ''
    );

    // --- 8. Clear the cart from the session ---
    unset($_SESSION['cart']);
    unset($_SESSION['request_catatan']);
    unset($_SESSION['request_jawatan']);

    // --- 9. Send the "Success" JSON response ---
    echo json_encode([
        'success' => true,
        'message' => "Permohonan anda telah berjaya dihantar."
    ]);
    exit;

} catch (Exception $e) {
    // --- 9. If anything fails, roll back the transaction ---
    $conn->rollback();

    // Send an error JSON response
    echo json_encode([
        'success' => false, 
        'message' => safeError('Gagal menghantar borang. Sila cuba lagi.', $e->getMessage())
    ]);
    exit;
}
?>