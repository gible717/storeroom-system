<?php
// FILE: department_process.php
require 'admin_auth_check.php';

// --- ADD ACTION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    
    $nama_jabatan = $_POST['nama_jabatan'];
    if (empty($nama_jabatan)) {
        header("Location: admin_departments.php?error=" . urlencode("Nama jabatan diperlukan."));
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO jabatan (nama_jabatan) VALUES (?)");
    $stmt->bind_param("s", $nama_jabatan);
    
    if ($stmt->execute()) {
        header("Location: admin_departments.php?success=" . urlencode("Jabatan baru berjaya ditambah."));
    } else {
        header("Location: admin_departments.php?error=" . urlencode("Gagal menambah jabatan."));
    }
    $stmt->close();

// --- EDIT ACTION ---
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {

    $nama_jabatan = $_POST['nama_jabatan'];
    $id_jabatan = $_POST['id_jabatan'];
    
    if (empty($nama_jabatan) || empty($id_jabatan)) {
        header("Location: admin_departments.php?error=" . urlencode("Maklumat tidak lengkap."));
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE jabatan SET nama_jabatan = ? WHERE ID_jabatan = ?");
    $stmt->bind_param("si", $nama_jabatan, $id_jabatan);
    
    if ($stmt->execute()) {
        header("Location: admin_departments.php?success=" . urlencode("Jabatan berjaya dikemaskini."));
    } else {
        header("Location: admin_departments.php?error=" . urlencode("Gagal mengemaskini jabatan."));
    }
    $stmt->close();

// --- DELETE ACTION ---
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    
    $id_jabatan = $_GET['id'];
    
    // TODO: Add a check here to see if any user is still in this department
    
    $stmt = $conn->prepare("DELETE FROM jabatan WHERE ID_jabatan = ?");
    $stmt->bind_param("i", $id_jabatan);
    
    if ($stmt->execute()) {
        header("Location: admin_departments.php?success=" . urlencode("Jabatan berjaya dipadam."));
    } else {
        header("Location: admin_departments.php?error=" . urlencode("Gagal memadam jabatan. Mungkin masih ada staf yang berdaftar di bawahnya."));
    }
    $stmt->close();

} else {
    // No valid action
    header("Location: admin_departments.php");
}

$conn->close();
exit;
?>