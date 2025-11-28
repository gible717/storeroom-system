<?php
// kewps8_form_process.php - Handle KEW.PS-8 form submission (AJAX)

session_start();
require_once __DIR__ . '/db.php';

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
$id_jabatan = (int)$user['ID_jabatan'];
$tarikh_mohon = date('Y-m-d'); // Current date

// --- 3. Database Transaction ---
$conn->begin_transaction();

try {
    // --- 4. Insert into `permohonan` (Header Table) ---
    $sql_header = "INSERT INTO permohonan 
                (tarikh_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan)
                VALUES (?, 'Baru', ?, ?, ?, ?, ?)";
    $stmt_header = $conn->prepare($sql_header);
    
    // Bind parameters (s = string, i = integer)
    $stmt_header->bind_param("ssssis", 
        $tarikh_mohon, 
        $staff_id, 
        $nama_pemohon, 
        $jawatan_pemohon, 
        $id_jabatan, 
        $catatan
    );
    
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

        $stmt_items->bind_param("iii", $id_permohonan_baru, $no_kod, $kuantiti_mohon);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // --- 6. If everything is OK, commit the transaction ---
    $conn->commit();

    // --- 7. Clear the cart from the session ---
    unset($_SESSION['cart']);
    unset($_SESSION['request_catatan']);
    unset($_SESSION['request_jawatan']); // <-- ADD THIS

    // --- 8. Send the "Success" JSON response ---
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
        'message' => 'Gagal menghantar borang. Sila cuba lagi. Ralat: ' . $e->getMessage()
    ]);
    exit;
}
?>