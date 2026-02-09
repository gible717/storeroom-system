<?php
// department_add.php - Add new department form

$pageTitle = "Tambah Jabatan";
require 'admin_header.php';

// Get error message and form data from session/query
$error = isset($_GET['error']) ? $_GET['error'] : null;
$error_field = isset($_SESSION['error_field']) ? $_SESSION['error_field'] : null;
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear session data after retrieving
unset($_SESSION['error_field']);
unset($_SESSION['form_data']);
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="admin_department.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Tambah Jabatan Baru</h3>
    </div>
    <div></div>
</div>

<!-- Error Alert -->
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="max-width: 600px; margin: 0 auto 1rem auto;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Add Form -->
<div class="card shadow-sm border-0" style="border-radius: 1rem; max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4 p-md-5">
        <form action="department_process.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo ($error_field === 'nama_jabatan') ? 'is-invalid' : ''; ?>"
                    id="nama_jabatan" name="nama_jabatan"
                    placeholder="Cth: Jabatan Kewangan"
                    value="<?php echo isset($form_data['nama_jabatan']) ? htmlspecialchars($form_data['nama_jabatan']) : ''; ?>"
                    required>
                <?php if ($error_field === 'nama_jabatan'): ?>
                <div class="invalid-feedback">Nama jabatan ini sudah wujud. Sila gunakan nama yang lain.</div>
                <?php endif; ?>
            </div>

            <div class="text-end mt-4">
                <a href="admin_department.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
