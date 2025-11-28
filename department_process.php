<?php
// department_process.php - Handle department add/edit/delete

require 'admin_auth_check.php';

// Add department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {

    $nama_jabatan = $_POST['nama_jabatan'];
    if (empty($nama_jabatan)) {
        header("Location: admin_department.php?error=" . urlencode("Nama jabatan diperlukan."));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO jabatan (nama_jabatan) VALUES (?)");
    $stmt->bind_param("s", $nama_jabatan);

    if ($stmt->execute()) {
        header("Location: admin_department.php?success=" . urlencode("Jabatan baru berjaya ditambah."));
    } else {
        header("Location: admin_department.php?error=" . urlencode("Gagal menambah jabatan."));
    }
    $stmt->close();

// Edit department
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {

    $nama_jabatan = $_POST['nama_jabatan'];
    $id_jabatan = $_POST['id_jabatan'];

    if (empty($nama_jabatan) || empty($id_jabatan)) {
        header("Location: admin_department.php?error=" . urlencode("Maklumat tidak lengkap."));
        exit;
    }

    $stmt = $conn->prepare("UPDATE jabatan SET nama_jabatan = ? WHERE ID_jabatan = ?");
    $stmt->bind_param("si", $nama_jabatan, $id_jabatan);

    if ($stmt->execute()) {
        header("Location: admin_department.php?success=" . urlencode("Jabatan berjaya dikemaskini."));
    } else {
        header("Location: admin_department.php?error=" . urlencode("Gagal mengemaskini jabatan."));
    }
    $stmt->close();

// Delete department
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {

    $id_jabatan = $_GET['id'];

    // Check if any staff are in this department
    $check_sql = "SELECT COUNT(*) as count FROM staf WHERE id_jabatan = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id_jabatan);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $staff_count = $row['count'];
    $check_stmt->close();

    if ($staff_count > 0) {
        header("Location: admin_department.php?error=" . urlencode("Tidak boleh padam! Jabatan ini masih mempunyai ($staff_count) orang staf."));
        exit;
    }

    // Delete department
    $stmt = $conn->prepare("DELETE FROM jabatan WHERE ID_jabatan = ?");
    $stmt->bind_param("i", $id_jabatan);

    if ($stmt->execute()) {
        header("Location: admin_department.php?success=" . urlencode("Jabatan berjaya dipadam."));
    } else {
        header("Location: admin_department.php?error=" . urlencode("Gagal memadam jabatan."));
    }
    $stmt->close();

} else {
    header("Location: admin_department.php");
}

$conn->close();
exit;
?>
