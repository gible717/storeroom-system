<?php
// FILE: request_form_process.php (Updated for Session Department)
session_start(); // Ensure session is started
require 'staff_auth_check.php'; // Includes db.php and security check

// Only allow POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: staff_dashboard.php');
    exit;
}

// 1. Get and sanitize data
$ID_staf = $_SESSION['ID_staf'] ?? null; // Get from session
$ID_produk = $_POST['ID_produk'] ?? '';
$jumlah_diminta = (int)($_POST['jumlah_diminta'] ?? 0);
$catatan = trim($_POST['catatan'] ?? null);
if ($catatan === '') $catatan = null; // Store empty string as NULL

// --- FIX 1: Get Department ID from Session ---
$ID_jabatan = $_SESSION['ID_jabatan'] ?? null; // Get ID from session

// --- FIX 2: Updated Validation ---
// Ensure required fields and session variables are present
if (empty($ID_staf) || empty($ID_jabatan) || empty($ID_produk) || $jumlah_diminta <= 0) {
    // Redirect with a more specific error if possible
    if (empty($ID_jabatan)) {
        $error_message = 'Maklumat jabatan tidak ditemui. Sila log masuk semula.';
    } else {
        $error_message = 'Sila lengkapkan semua medan yang diperlukan.';
    }
    header('Location: request_form.php?error=' . urlencode($error_message));
    exit;
}

// 3. Advanced Validation: Check if requested quantity exceeds available stock
$stmt_check = $conn->prepare("SELECT stok_semasa FROM produk WHERE ID_produk = ?");
if (!$stmt_check) {
    header('Location: request_form.php?error=' . urlencode('Ralat pangkalan data (semak stok).'));
    exit;
}
$stmt_check->bind_param('s', $ID_produk);
$stmt_check->execute();
$product = $stmt_check->get_result()->fetch_assoc();
$baki_sedia_ada = $product ? (int)$product['stok_semasa'] : 0;
$stmt_check->close(); // Close this statement

if ($jumlah_diminta > $baki_sedia_ada) {
    header('Location: request_form.php?error=' . urlencode('Kuantiti diminta (' . $jumlah_diminta . ') melebihi stok sedia ada (' . $baki_sedia_ada . ').'));
    exit;
}

// 4. Set initial values for the new request
$status = 'BARU'; // Use 'BARU' instead of 'Belum Diproses' for consistency if needed
$tarikh_mohon = date('Y-m-d H:i:s'); // Use DATETIME for more precision

// --- FIX 3: Updated SQL INSERT ---
// Assuming your 'permohonan' table uses 'ID_jabatan' (INT)
$sql = "INSERT INTO permohonan (ID_staf, ID_jabatan, ID_produk, tarikh_mohon, status, jumlah_diminta, baki_sedia_ada, catatan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: request_form.php?error=' . urlencode('Ralat pangkalan data (prepare insert).'));
    exit;
}

// --- FIX 4: Updated bind_param ---
// Changed parameter types: s i s s s i i s (assuming ID_staf is VARCHAR, ID_jabatan INT, ID_produk VARCHAR, ...)
// Adjust types if your table structure differs (e.g., if ID_staf is INT, change first 's' to 'i')
$stmt->bind_param('sisssiis',
    $ID_staf,
    $ID_jabatan, // Use the ID from session
    $ID_produk,
    $tarikh_mohon,
    $status,
    $jumlah_diminta,
    $baki_sedia_ada,
    $catatan
);

if ($stmt->execute()) {
    $message = urlencode("Permohonan anda telah berjaya dihantar.");
    header("Location: request_list.php?success=" . $message);
} else {
    $message = urlencode("Gagal menghantar permohonan. Ralat: " . $stmt->error); // Include DB error for debugging
    header("Location: request_form.php?error=" . $message);
}

$stmt->close();
$conn->close();
exit;
?>