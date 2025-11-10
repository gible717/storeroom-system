<?php
// FILE: kewps8_form_process.php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in and is a staff member
if (!isset($_SESSION['ID_staf']) || $_SESSION['peranan'] != 'Staf') {
    // Redirect to login if not logged in or not a staff
    header('Location: login.php');
    exit;
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get Data from Session & Form ---
    $staff_id = $_SESSION['ID_staf'];
    
    // The 'items' array from the form
    $items = $_POST['items'] ?? [];
    $catatan = $_POST['catatan'] ?? null;

    // Validation: Check if at least one item was submitted
    if (empty($items)) {
        // No items were submitted, send back an error
        $_SESSION['error_msg'] = "Sila tambah sekurang-kurangnya satu barang.";
        header('Location: kewps8_form.php');
        exit;
    }

    // --- 2. Get Full Staff Details (We need name, jawatan, ID_jabatan) ---
    $stmt = $conn->prepare("SELECT nama, jawatan, ID_jabatan FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        // This should not happen if user is logged in, but it's a good safety check
        die("Ralat: Data staf tidak dijumpai.");
    }

    $nama_pemohon = $user['nama'];
    $jawatan_pemohon = $user['jawatan'];
    $id_jabatan = $user['ID_jabatan'];
    $tarikh_mohon = date('Y-m-d'); // Current date

    // --- 3. Database Transaction ---
    // We use a transaction because we need to insert into TWO tables.
    // If one fails, we can roll back both.
    $conn->begin_transaction();

    try {
        // --- 4. Insert into `permohonan` (Header Table) ---
        // --- EDITED: Added 'catatan' to the SQL query ---
        $sql_header = "INSERT INTO permohonan 
                    (tarikh_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan)
                    VALUES (?, 'Baru', ?, ?, ?, ?, ?)";
        $stmt_header = $conn->prepare($sql_header);
        // --- EDITED: Added 's' for catatan and $catatan variable ---
        $stmt_header->bind_param("ssssis", $tarikh_mohon, $ID_staf, $nama_pemohon, $jawatan_pemohon, $ID_jabatan, $catatan);
        $stmt_header->execute();

        // Get the ID of the new request we just created
        $id_permohonan_baru = $conn->insert_id;
        $stmt_header->close();

        // --- 5. Insert into `permohonan_barang` (Detail Table) ---
        $sql_items = "INSERT INTO permohonan_barang 
                    (ID_permohonan, no_kod, kuantiti_mohon) 
                    VALUES (?, ?, ?)";
        
        $stmt_items = $conn->prepare($sql_items);

        // Loop through each item submitted from the form
        foreach ($items as $item) {
            $no_kod = $item['no_kod'];
            $kuantiti_mohon = $item['kuantiti_mohon'];

            // Bind the values and execute for each item
            $stmt_items->bind_param("iii", $id_permohonan_baru, $no_kod, $kuantiti_mohon);
            $stmt_items->execute();
        }
        $stmt_items->close();

        // --- 6. If everything is OK, commit the transaction ---
        $conn->commit();

        // Set a success message and redirect to the list page
        $_SESSION['success_msg'] = "Permohonan anda (ID: $id_permohonan_baru) telah berjaya dihantar.";
        // We will create kewps8_list.php next
        header('Location: kewps8_list.php'); 
        exit;

    } catch (Exception $e) {
        // --- 7. If anything fails, roll back the transaction ---
        $conn->rollback();

        // Log the error (optional, but good practice)
        // error_log("Error in kewps8_form_process.php: " . $e->getMessage());

        // Send user back with an error message
        $_SESSION['error_msg'] = "Gagal menghantar borang. Sila cuba lagi. Ralat: " . $e->getMessage();
        header('Location: kewps8_form.php');
        exit;
    }

} else {
    // Not a POST request, redirect to the form
    header('Location: request_list.php');
    exit;
}
?>