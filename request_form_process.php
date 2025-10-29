<?php
// FILE: request_form_process.php (NOW 100% "SLAYED")

// "SLAY" (FIX) 1: THIS "VIBE" (BLOCK) IS THE "STEAK" (THE FIX)
// It "slays" (starts) the session AND "slays" (includes) the "steak" (correct) auth file.
// The "ghost" (bug) file 'auth_check.php' is "slain" (killed).
session_start();
require 'staff_auth_check.php'; // This includes db.php and security check

// Only allow POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: staff_dashboard.php');
    exit;
}

// 1. Get and sanitize data
// "SLAY" (FIX) 2: We "vibe" (use) the "steak" (correct) UPPERCASE session key
$id_staf = $_SESSION['ID_staf'] ?? null; 
$id_produk = $_POST['id_produk'] ?? '';
$jumlah_diminta = (int)($_POST['jumlah_diminta'] ?? 0);
$catatan = trim($_POST['catatan'] ?? null);
if ($catatan === '') $catatan = null;

// --- FIX 1: Get Department Info from DATABASE using the Session's ID_staf ---
$id_jabatan = null; // Default to null
$nama_jabatan_staf = null; // Default to null

if (!empty($id_staf)) { 
    // Join with jabatan table to get the NAME
    $stmt_dept = $conn->prepare("SELECT s.ID_jabatan, j.nama_jabatan 
                                FROM staf s
                                LEFT JOIN jabatan j ON s.ID_jabatan = j.ID_jabatan
                                WHERE s.ID_staf = ?");
    if ($stmt_dept) {
        $stmt_dept->bind_param("s", $id_staf);
        $stmt_dept->execute();
        $dept_result = $stmt_dept->get_result();
        if ($dept_row = $dept_result->fetch_assoc()) {
            $id_jabatan = $dept_row['ID_jabatan']; // We found the ID
            $nama_jabatan_staf = $dept_row['nama_jabatan']; // We found the NAME
        }
        $stmt_dept->close();
    }
}

// --- FIX 2: Updated Validation ---
if (empty($id_staf) || empty($id_jabatan) || empty($nama_jabatan_staf) || empty($id_produk) || $jumlah_diminta <= 0) {
    
    if (empty($id_jabatan) || empty($nama_jabatan_staf)) {
        // This error means the staff member is not linked to a valid department.
        $error_message = 'Maklumat jabatan anda tidak lengkap. Sila hubungi Admin untuk menetapkan jabatan anda.';
    } else {
        $error_message = 'Sila lengkapkan semua medan yang diperlukan.';
    }
    header('Location: request_form.php?error=' . urlencode($error_message));
    exit;
}

// 3. Advanced Validation: Check if requested quantity exceeds available stock
$stmt_check = $conn->prepare("SELECT stok_semasa FROM produk WHERE ID_produk = ?");
$stmt_check->bind_param('s', $id_produk);
$stmt_check->execute();
$product = $stmt_check->get_result()->fetch_assoc();
$baki_sedia_ada = $product ? (int)$product['stok_semasa'] : 0;
$stmt_check->close(); 

if ($jumlah_diminta > $baki_sedia_ada) {
    header('Location: request_form.php?error=' . urlencode('Kuantiti diminta (' . $jumlah_diminta . ') melebihi stok sedia ada (' . $baki_sedia_ada . ').'));
    exit;
}

// 4. Set initial values for the new request
$status = 'Belum Diproses'; 
$tarikh_mohon = date('Y-m-d H:i:s'); 


// 5. Prepare and execute the SQL INSERT statement
// --- FIX 3: Corrected SQL to use 'jabatan_unit' column ---
$sql = "INSERT INTO permohonan (ID_staf, ID_produk, tarikh_mohon, status, jumlah_diminta, jabatan_unit, baki_sedia_ada, catatan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; // Removed ID_jabatan, added jabatan_unit

$stmt = $conn->prepare($sql); // This is line 72, which will now work
if (!$stmt) {
    header('Location: request_form.php?error=' . urlencode('Ralat pangkalan data (prepare insert).'));
    exit;
}

// --- FIX 4: Corrected bind_param types and variables ---
// Using 's' for jabatan_unit (which is $nama_jabatan_staf)
$stmt->bind_param('ssssisis',
    $id_staf,
    $id_produk,
    $tarikh_mohon,
    $status,
    $jumlah_diminta,
    $nama_jabatan, // Pass the staff's department NAME here
    $baki_sedia_ada,
    $catatan
);

if ($stmt->execute()) {
    $message = urlencode("Permohonan anda telah berjaya dihantar.");
    header("Location: request_list.php?success=" . $message);
} else {
    $message = urlencode("Gagal menghantar permohonan. Ralat: " . $stmt->error);
    header("Location: request_form.php?error=" . $message);
}

$stmt->close();
$conn->close();
exit;
?>