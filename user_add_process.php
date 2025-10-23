<?php
// FILE: user_add_process.php
require 'admin_auth_check.php';

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get all form data
    $id_staf = $_POST['id_staf'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $emel = $_POST['emel'] ?? '';
    $id_jabatan = $_POST['id_jabatan'] ?? '';
    $peranan = $_POST['peranan'] ?? '';
    $password = $_POST['password'] ?? ''; // This is the temporary password
    
    // 2. --- Server-side Validation ---
    if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || empty($peranan) || empty($password)) {
        header("Location: user_add.php?error=" . urlencode("Sila isi semua ruangan yang bertanda *."));
        exit;
    }
    
    // 3. --- Check for Duplicates ---
    // Check if ID Staf already exists
    $stmt_check_id = $conn->prepare("SELECT ID_staf FROM staf WHERE ID_staf = ?");
    $stmt_check_id->bind_param("s", $id_staf);
    $stmt_check_id->execute();
    if ($stmt_check_id->get_result()->num_rows > 0) {
        header("Location: user_add.php?error=" . urlencode("ID Staf ini telah wujud."));
        $stmt_check_id->close();
        exit;
    }
    $stmt_check_id->close();
    
    // Check if Emel already exists
    $stmt_check_emel = $conn->prepare("SELECT emel FROM staf WHERE emel = ?");
    $stmt_check_emel->bind_param("s", $emel);
    $stmt_check_emel->execute();
    if ($stmt_check_emel->get_result()->num_rows > 0) {
        header("Location: user_add.php?error=" . urlencode("Emel ini telah wujud."));
        $stmt_check_emel->close();
        exit;
    }
    $stmt_check_emel->close();
    
    // 4. --- Process and Insert Data ---
    
    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set is_first_login to 1 (true)
    $is_first_login = 1;
    
    // Prepare the final SQL INSERT statement
    $sql = "INSERT INTO staf (ID_staf, nama, emel, ID_jabatan, peranan, kata_laluan, is_first_login) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    // 's' = string, 'i' = integer
    $stmt->bind_param("sssisbi", $id_staf, $nama, $emel, $id_jabatan, $peranan, $hashed_password, $is_first_login);
    
    if ($stmt->execute()) {
        header("Location: admin_users.php?success=" . urlencode("Pengguna baru berjaya ditambah."));
    } else {
        header("Location: user_add.php?error=" . urlencode("Gagal menambah pengguna. Sila cuba lagi."));
    }
    
    $stmt->close();
    $conn->close();
    exit;

} else {
    // Not a POST request, redirect to user list
    header("Location: admin_users.php?success=Maklumat pengguna berjaya dikemaskini!");
    exit;
}
?>