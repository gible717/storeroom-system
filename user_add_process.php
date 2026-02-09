<?php
// user_add_process.php - Handle new user creation

require 'db.php';
session_start();
require_once 'csrf.php';

// Check admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Akses ditolak.");
}

// Validate CSRF token
csrf_check('admin_users.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = !empty($_POST['emel']) ? $_POST['emel'] : null; // Email is optional
    $id_jabatan = $_POST['id_jabatan'];
    $is_admin = $_POST['is_admin'];
    $kata_laluan_sementara = $_POST['kata_laluan_sementara'];

    // Validate required fields (email is now optional)
    if (empty($id_staf) || empty($nama) || empty($id_jabatan) || !isset($is_admin) || empty($kata_laluan_sementara)) {
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

    try {
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: admin_users.php?success=Pengguna baru berjaya dicipta.");
        exit();
    } catch (mysqli_sql_exception $e) {
        $stmt->close();
        $conn->close();

        // Check for duplicate entry error (MySQL error code 1062)
        if ($e->getCode() == 1062) {
            // Store form data in session for repopulating
            $_SESSION['form_data'] = [
                'id_staf' => $id_staf,
                'nama' => $nama,
                'emel' => $emel,
                'id_jabatan' => $id_jabatan,
                'is_admin' => $is_admin
            ];

            // Determine which field caused the duplicate
            if (strpos($e->getMessage(), 'PRIMARY') !== false || strpos($e->getMessage(), 'ID_staf') !== false) {
                $_SESSION['error_field'] = 'id_staf';
                header("Location: user_add.php?error=" . urlencode("ID Staf '$id_staf' sudah wujud dalam sistem. Sila gunakan ID yang lain."));
            } elseif (strpos($e->getMessage(), 'emel') !== false) {
                $_SESSION['error_field'] = 'emel';
                header("Location: user_add.php?error=" . urlencode("Emel '$emel' sudah digunakan. Sila gunakan emel yang lain."));
            } else {
                header("Location: user_add.php?error=" . urlencode("Data yang dimasukkan sudah wujud dalam sistem."));
            }
        } else {
            header("Location: user_add.php?error=" . urlencode("Gagal mencipta pengguna. Ralat pangkalan data."));
        }
        exit();
    }

} else {
    header("Location: user_add.php");
    exit();
}
?>
