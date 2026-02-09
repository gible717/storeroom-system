<?php
// staff_register_process.php - Handle staff registration

session_start();
require 'db.php';
require_once 'csrf.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

// Validate CSRF token
if (!csrf_validate()) {
    echo json_encode(['success' => false, 'message' => 'Sesi anda telah tamat. Sila muat semula halaman.']);
    exit;
}

    // Get form data
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $id_jabatan = $_POST['id_jabatan'];
    $kata_laluan = $_POST['kata_laluan'];
    $sahkan_kata_laluan = $_POST['sahkan_kata_laluan'];

    // Validate passwords match
    if ($kata_laluan !== $sahkan_kata_laluan) {
        echo json_encode([
            'success' => false,
            'message' => 'Kata laluan tidak sepadan.'
        ]);
        exit();
    }

    // Check duplicate ID
    $stmt = $conn->prepare("SELECT ID_staf FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $id_staf);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID Staf ini telah wujud. Sila guna ID lain.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Check duplicate email
    $stmt = $conn->prepare("SELECT emel FROM staf WHERE emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Emel ini telah didaftarkan. Sila guna emel lain.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();


    // 3. Process the data (if all checks passed)
    
    // Hash the password securely
    $hashed_password = password_hash($kata_laluan, PASSWORD_BCRYPT);

    // Set the default role (staff registration always creates non-admin users)
    $is_admin = 0;
    $is_first_login = 0;

    $sql = "INSERT INTO staf (ID_staf, nama, emel, ID_jabatan, kata_laluan, is_admin, is_first_login) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisii", $id_staf, $nama, $emel, $id_jabatan, $hashed_password, $is_admin, $is_first_login);
    if ($stmt->execute()) {
        // Success! Return JSON response for AJAX
        echo json_encode([
            'success' => true,
            'message' => 'Pendaftaran berjaya! Anda akan dibawa ke halaman log masuk...'
        ]);
        exit();
    } else {
        // Database error
        echo json_encode([
            'success' => false,
            'message' => 'Pendaftaran gagal disebabkan ralat pangkalan data. Sila cuba lagi.'
        ]);
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, just redirect to the register page
    header("Location: staff_register.php");
    exit();
}
?>