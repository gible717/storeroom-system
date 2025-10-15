<?php
// FILE: request_form_process.php
require 'auth_check.php';

// Only allow POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: staff_dashboard.php');
    exit;
}

// 1. Get and sanitize data from the form
$id_staf = $_SESSION['ID_staf'];
$jabatan_unit = trim($_POST['jabatan_unit'] ?? '');
$id_produk = $_POST['id_produk'] ?? '';
$no_bpsi = trim($_POST['no_bpsi'] ?? null);
$jumlah_diminta = (int)($_POST['jumlah_diminta'] ?? 0);
$catatan = trim($_POST['catatan'] ?? null);

// 2. Simple Validation: Ensure required fields are not empty
if (empty($jabatan_unit) || empty($id_produk) || $jumlah_diminta <= 0) {
    header('Location: request_form.php?error=' . urlencode('Sila lengkapkan semua medan yang diperlukan.'));
    exit;
}

// 3. Advanced Validation: Check if requested quantity exceeds available stock
$stmt = $conn->prepare("SELECT stok_semasa FROM produk WHERE ID_produk = ?");
$stmt->bind_param('s', $id_produk);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$baki_sedia_ada = $product ? (int)$product['stok_semasa'] : 0;

if ($jumlah_diminta > $baki_sedia_ada) {
    header('Location: request_form.php?error=' . urlencode('Kuantiti diminta melebihi stok sedia ada.'));
    exit;
}

// 4. Set initial values for the new request
$status = 'Belum Diproses'; // Default status for all new requests
$tarikh_mohon = date('Y-m-d'); // Today's date

// 5. Prepare and execute the SQL INSERT statement
$sql = "INSERT INTO permohonan (ID_staf, ID_produk, tarikh_mohon, status, jumlah_diminta, no_bpsi, jabatan_unit, baki_sedia_ada, catatan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssisiss', $id_staf, $id_produk, $tarikh_mohon, $status, $jumlah_diminta, $no_bpsi, $jabatan_unit, $baki_sedia_ada, $catatan);

if ($stmt->execute()) {
    // Success! Redirect to the request list page with a success message.
    header('Location: request_list.php?success=' . urlencode('Permohonan berjaya dihantar.'));
    exit;
} else {
    // Fail! Redirect back to the form with an error.
    header('Location: request_form.php?error=' . urlencode('Gagal menghantar permohonan. Sila cuba lagi.'));
    exit;
}
?>