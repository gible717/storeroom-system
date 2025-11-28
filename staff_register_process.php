<?php
// staff_register_process.php - Handle staff registration

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $id_staf = $_POST['id_staf'];
    $nama = $_POST['nama'];
    $emel = $_POST['emel'];
    $id_jabatan = $_POST['id_jabatan'];
    $kata_laluan = $_POST['kata_laluan'];
    $sahkan_kata_laluan = $_POST['sahkan_kata_laluan'];

    // Validate passwords match
    if ($kata_laluan !== $sahkan_kata_laluan) {
        $error = "Kata laluan tidak sepadan.";
        header("Location: staff_register.php?error=" . urlencode($error));
        exit();
    }

    // Check duplicate ID
    $stmt = $conn->prepare("SELECT ID_staf FROM staf WHERE ID_staf = ?");
    $stmt->bind_param("s", $id_staf);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "ID Staf ini telah wujud. Sila guna ID lain.";
        header("Location: staff_register.php?error=" . urlencode($error));
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
        $error = "Emel ini telah didaftarkan. Sila guna emel lain.";
        header("Location: staff_register.php?error=" . urlencode($error));
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();


    // 3. Process the data (if all checks passed)
    
    // Hash the password securely
    $hashed_password = password_hash($kata_laluan, PASSWORD_BCRYPT);
    
    // Set the default role
    $peranan = 'Staf';
    $is_first_login = 0;

   // THIS IS THE FIX
    $sql = "INSERT INTO staf (ID_staf, nama, emel, ID_jabatan, katalaluan, peranan, is_first_login) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // === IMPORTANT: CHECK THIS LINE ===
    // This assumes ID_jabatan is an INTEGER (i). 
    // THIS IS THE FIX: Add "i" for the integer and the $is_first_login variable
    $stmt->bind_param("sssissi", $id_staf, $nama, $emel, $id_jabatan, $hashed_password, $peranan, $is_first_login);
    if ($stmt->execute()) {
        // Success! Redirect to login page with a success message
        $success = "Pendaftaran berjaya! Sila log masuk dengan akaun baru anda.";
        header("Location: login.php?success=" . urlencode($success));
        exit();
    } else {
        // Database error
        $error = "Pendaftaran gagal disebabkan ralat pangkalan data. Sila cuba lagi.";
        header("Location: staff_register.php?error=" . urlencode($error));
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