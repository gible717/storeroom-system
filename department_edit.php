<?php
// department_edit.php - Edit department form

$pageTitle = "Kemaskini Jabatan";
require 'admin_header.php';

// Get department ID from URL
$edit_id = $_GET['id'] ?? null;
if (!$edit_id) {
    header("Location: admin_department.php?error=" . urlencode("ID tidak sah."));
    exit;
}

// Fetch department data
$stmt_edit = $conn->prepare("SELECT nama_jabatan FROM jabatan WHERE ID_jabatan = ?");
$stmt_edit->bind_param("i", $edit_id);
$stmt_edit->execute();
$result = $stmt_edit->get_result();
if ($result->num_rows == 0) {
    header("Location: admin_department.php?error=" . urlencode("Jabatan tidak ditemui."));
    exit;
}
$edit_name = $result->fetch_assoc()['nama_jabatan'];
$stmt_edit->close();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="admin_department.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Kemaskini Jabatan</h3>
    </div>
    <div></div>
</div>

<!-- Edit Form -->
<div class="card shadow-sm border-0" style="border-radius: 1rem; max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4 p-md-5">
        <form action="department_process.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_jabatan" value="<?php echo $edit_id; ?>">

            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan"
                    value="<?php echo htmlspecialchars($edit_name); ?>" required>
            </div>

            <div class="text-end mt-4">
                <a href="admin_department.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kemaskini</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
require 'admin_footer.php';
?>
