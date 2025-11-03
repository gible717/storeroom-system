<?php
// FILE: user_add_process.php (FIXED)
require 'db.php'; 
session_start(); 

// Admin-only security check
if (!isset($_SESSION['peranan']) || $_SESSION['peranan'] != 'Admin') {
    die("Akses ditolak.");
}

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get all data from the form
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $id_jabatan = $_POST['id_jabatan'];
    $peranan = $_POST['peranan'];
    
    // This is the variable from your hidden "User123" input field
    $kata_laluan_sementara = $_POST['kata_laluan_sementara']; 

    // 2. Validate data
    if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || empty($peranan) || empty($kata_laluan_sementara)) {
        header("Location: user_add.php?error=Sila lengkapkan semua medan.");
        exit();
    }

    // 3. Hash the password
    $hashed_password = password_hash($kata_laluan_sementara, PASSWORD_BCRYPT);
    
    // 4. Set is_first_login to 1 (true)
    $is_first_login = 1;

    // 5. Insert into the database
    // THIS IS THE CORRECTED QUERY with 'katalaluan' and 'is_first_login'
    $sql = "INSERT INTO staf (
                ID_staf, 
                nama, 
                emel, 
                ID_jabatan, 
                peranan, 
                katalaluan,  -- <-- This was the problem
                is_first_login -- <-- This was the problem
            ) VALUES (?, ?, ?, ?, ?, ?, ?)"; // 7 question marks
    
    $stmt = $conn->prepare($sql);
    
    // 6. THIS IS THE CORRECTED BIND_PARAM
    // s(ID_staf), s(nama), s(emel), i(ID_jabatan), s(peranan), s(hashed_password), i(is_first_login)
    $stmt->bind_param("sssissi", 
        $id_staf, 
        $nama, 
        $emel, 
        $id_jabatan, 
        $peranan, 
        $hashed_password, 
        $is_first_login
    );

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