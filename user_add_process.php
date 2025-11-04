<?php
// FILE: user_add_process.php (FIXED)
require 'db.php'; 
session_start(); 

// --- "STEAK" (FIX) #1: Check the NEW session variable ---
// We now check if the user 'is_admin'
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Akses ditolak.");
}
$is_superadmin = $_SESSION['is_superadmin'] ?? 0;
// ---------------- END OF FIX -------------------

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get all data from the form
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $id_jabatan = $_POST['id_jabatan'];
    $is_admin = $_POST['is_admin']; // This is correct from your file

// --- "STEAK" (FIX): "4x4" (Safe) Security Check ---
// If the logged-in user is NOT a super admin, "slay" (force) any new user to be a Staf.
    if (!$is_superadmin) {
       $is_admin = 0; // "Slay" (force) them to be Staf (is_admin = 0)
    }
// ---------------- END OF FIX ----------------

    $is_superadmin = 0; // New users are never Super Admins
    
    $kata_laluan_sementara = $_POST['kata_laluan_sementara']; 

    // --- "STEAK" (FIX) #2: Validate the NEW variable ---
    // We remove 'empty($peranan)' and check if 'is_admin' was set
    if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || !isset($is_admin) || empty($kata_laluan_sementara)) {
        header("Location: user_add.php?error=Sila lengkapkan semua medan.");
        exit();
    }
    // ---------------- END OF FIX -------------------

    // 3. Hash the password
    $hashed_password = password_hash($kata_laluan_sementara, PASSWORD_BCRYPT);
    
    // 4. Set is_first_login to 1 (true)
    $is_first_login = 1;

    // 5. Insert into the database
    $sql = "INSERT INTO staf (ID_staf, nama, emel, ID_jabatan, katalaluan, is_first_login, is_admin, is_superadmin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // 6. Bind parameters (this was already correct)
    $stmt->bind_param("sssissii", $id_staf, $nama, $emel, $id_jabatan, $hashed_password, $is_first_login, $is_admin, $is_superadmin);

    // 7. Execute and redirect
    if ($stmt->execute()) {
        header("Location: admin_users.php?success=Pengguna baru berjaya dicipta.");
    } else {
        header("Location: user_add.php?error=Gagal mencipta pengguna. Ralat pangkalan data.");
    }
    
    $stmt->close();
    $conn->close();
    exit();

} else {
    // Not a POST request
    header("Location: user_add.php");
    exit();
}
?>