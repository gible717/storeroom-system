<?php
// FILE: admin_category_process.php (NOW "SLAYED" WITH NEW NAME)
session_start();
require_once 'db.php';
require 'admin_header.php'; // This "slays" (includes) db.php and auth checks

// "4x4" (Safe) Check: Ensure user is Admin
$userRole = isset($_SESSION['userRole']) ? $_SESSION['userRole'] : null;
if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php?error=Akses tidak dibenarkan.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // "Kernel" (Logic) for ADDING
    if ($action == 'add' && !empty($_POST['nama_kategori'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $stmt = $conn->prepare("INSERT INTO KATEGORI (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama_kategori);

        if ($stmt->execute()) {
            // "SLAY" (FIX) 3: Redirect is "slain" (fixed)
            header("Location: admin_category.php?success=Kategori berjaya ditambah!");
        } else {
            if ($conn->errno == 1062) { // 1062 = Duplicate entry
                header("Location: admin_category.php?error=Kategori ini sudah wujud.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
        }
        $stmt->close();
    } 
    
    // "Kernel" (Logic) for DELETING
    else if ($action == 'delete' && !empty($_POST['ID_kategori'])) {
        $ID_kategori = (int)$_POST['ID_kategori'];
        $stmt = $conn->prepare("DELETE FROM KATEGORI WHERE ID_kategori = ?");
        $stmt->bind_param("i", $ID_kategori);
        
        if ($stmt->execute()) {
            // "SLAY" (FIX) 4: Redirect is "slain" (fixed)
            header("Location: admin_category.php?success=Kategori berjaya dipadam!");
        } else {
            if ($conn->errno == 1451) { // 1451 = Foreign Key constraint fails
                header("Location: admin_category.php?error=Tidak boleh padam. Kategori ini sedang digunakan oleh produk.");
            } else {
                header("Location: admin_category.php?error=Ralat pangkalan data: " . $conn->error);
            }
        }
        $stmt->close();
    }
    
    else {
        // "SLAY" (FIX) 5: Redirect is "slain" (fixed)
        header("Location: admin_category.php?error=Tindakan tidak sah.");
    }

} else {
    // "SLAY" (FIX) 6: Redirect is "slain" (fixed)
    header("Location: admin_category.php");
}

$conn->close();
exit;
?>