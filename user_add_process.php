<?php
// user_add_process.php - Handle new user creation

require 'db.php';
session_start();

// Check admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Akses ditolak.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $id_jabatan = $_POST['id_jabatan'];
    $is_admin = $_POST['is_admin'];
    $kata_laluan_sementara = $_POST['kata_laluan_sementara'];

    // Validate required fields
    if (empty($id_staf) || empty($nama) || empty($emel) || empty($id_jabatan) || !isset($is_admin) || empty($kata_laluan_sementara)) {
        header("Location: user_add.php?error=Sila lengkapkan semua medan.");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($kata_laluan_sementara, PASSWORD_BCRYPT);
    $is_first_login = 1;

    // Insert into database
    $sql = "INSERT INTO staf (ID_staf, nama, emel, ID_jabatan, kata_laluan, is_first_login, is_admin)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissi", $id_staf, $nama, $emel, $id_jabatan, $hashed_password, $is_first_login, $is_admin);

    if ($stmt->execute()) {
        header("Location: admin_users.php?success=Pengguna baru berjaya dicipta.");
    } else {
        header("Location: user_add.php?error=Gagal mencipta pengguna. Ralat pangkalan data.");
    }

    $stmt->close();
    $conn->close();
    exit();

} else {
    header("Location: user_add.php");
    exit();
}
?>
