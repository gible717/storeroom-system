<?php
// FILE: kewps8_form_process.php (VERSI 3.0 - Smart Form)
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

    // --- 1. Get Data from Session & Form (Smart Mode) ---
    $staff_id = $_SESSION['ID_staf']; // The user's ID from session
    $form_mode = $_POST['form_mode'] ?? 'single'; // Get the new hidden input
    $catatan = $_POST['catatan'] ?? null;
    $items = []; // Initialize an empty items array

    if ($form_mode === 'single') {
        // If it's single mode, manually build the $items array
        if (!empty($_POST['single_no_kod']) && !empty($_POST['single_kuantiti'])) {
            $items[1]['no_kod'] = $_POST['single_no_kod'];
            $items[1]['kuantiti_mohon'] = $_POST['single_kuantiti'];
        }
    } else {
        // If it's multi mode, just get the array like before
        $items = $_POST['items'] ?? [];
    }

    // Validation: Check if the $items array is *still* empty
    if (empty($items)) {
        // This catches both single-mode fails and multi-mode fails
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
        die("Ralat: Data staf tidak dijumpai.");
    }

    $nama_pemohon = $user['nama'];
    $jawatan_pemohon = $user['jawatan'];
    $id_jabatan = $user['ID_jabatan'];
    $tarikh_mohon = date('Y-m-d'); // Current date

    // --- 3. Database Transaction ---
    $conn->begin_transaction();

    try {
        // --- 4. Insert into `permohonan` (Header Table) ---
        $sql_header = "INSERT INTO permohonan 
                    (tarikh_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan)
                    VALUES (?, 'Baru', ?, ?, ?, ?, ?)";
        $stmt_header = $conn->prepare($sql_header);
        
        // ### THIS IS THE FINAL, CORRECTED LINE ###
        $stmt_header->bind_param("ssssis", $tarikh_mohon, $staff_id, $nama_pemohon, $jawatan_pemohon, (int)$id_jabatan, $catatan);
        
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

            $stmt_items->bind_param("iii", $id_permohonan_baru, $no_kod, $kuantiti_mohon);
            $stmt_items->execute();
        }
        $stmt_items->close();

        // --- 6. If everything is OK, commit the transaction ---
        $conn->commit();

        $_SESSION['success_msg'] = "Permohonan anda (ID: $id_permohonan_baru) telah berjaya dihantar.";
        header('Location: request_list.php'); // This is the correct success redirect
        exit;

    } catch (Exception $e) {
        // --- 7. If anything fails, roll back the transaction ---
        $conn->rollback();

        $_SESSION['error_msg'] = "Gagal menghantar borang. Sila cuba lagi. Ralat: " . $e->getMessage();
        header('Location: kewps8_form.php');
        exit;
    }

} else {
    // Not a POST request, redirect to the form
    header('Location: kewps8_form.php');
    exit;
}
?>