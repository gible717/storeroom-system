<?php
// user_add.php - Add new user form (Admin can create Admin or Staff)

$pageTitle = "Tambah Pengguna Baru";
require 'admin_header.php';

// Get departments for dropdown
$jabatan_result = $conn->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");

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
        <a href="admin_users.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Tambah Pengguna Baru</h3>
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

<!-- Add User Form -->
<div class="card shadow-sm border-0" style="border-radius: 1rem; max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4 p-md-5">
        <form action="user_add_process.php" method="POST">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label for="id_staf" class="form-label">ID Staf <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php echo ($error_field === 'id_staf') ? 'is-invalid' : ''; ?>"
                    id="id_staf" name="id_staf"
                    placeholder="Cth: 12345"
                    value="<?php echo isset($form_data['id_staf']) ? htmlspecialchars($form_data['id_staf']) : ''; ?>"
                    required>
                <?php if ($error_field === 'id_staf'): ?>
                <div class="invalid-feedback">ID Staf ini sudah wujud. Sila gunakan ID yang lain.</div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama"
                    placeholder="Cth: Ali Bin Ahmad"
                    value="<?php echo isset($form_data['nama']) ? htmlspecialchars($form_data['nama']) : ''; ?>"
                    required>
            </div>

            <div class="mb-3">
                <label for="emel" class="form-label">Emel</label>
                <input type="email" class="form-control <?php echo ($error_field === 'emel') ? 'is-invalid' : ''; ?>"
                    id="emel" name="emel"
                    placeholder="Cth: ali@mpk.gov.my"
                    value="<?php echo isset($form_data['emel']) ? htmlspecialchars($form_data['emel']) : ''; ?>">
                <?php if ($error_field === 'emel'): ?>
                <div class="invalid-feedback">Emel ini sudah digunakan. Sila gunakan emel yang lain.</div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="id_jabatan" class="form-label">Jabatan/Unit <span class="text-danger">*</span></label>
                <select class="form-select" id="id_jabatan" name="id_jabatan" required>
                    <option value="" selected disabled>-- Sila Pilih Jabatan --</option>
                    <?php while($jabatan = $jabatan_result->fetch_assoc()): ?>
                        <option value="<?php echo $jabatan['ID_jabatan']; ?>"
                            <?php echo (isset($form_data['id_jabatan']) && $form_data['id_jabatan'] == $jabatan['ID_jabatan']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jabatan['nama_jabatan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Role selection -->
            <div class="mb-3">
                <label for="is_admin" class="form-label">Peranan <span class="text-danger">*</span></label>
                <select class="form-select" id="is_admin" name="is_admin" required>
                    <option value="0" <?php echo (isset($form_data['is_admin']) && $form_data['is_admin'] == 0) ? 'selected' : ''; ?>>Staf</option>
                    <option value="1" <?php echo (isset($form_data['is_admin']) && $form_data['is_admin'] == 1) ? 'selected' : ''; ?>>Admin</option>
                </select>
                <div class="form-text">Pilih peranan untuk pengguna baru.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Laluan Sementara</label>
                <p class="form-control-plaintext"><strong>User123</strong></p>
                <input type="hidden" name="kata_laluan_sementara" value="User123">
                <div class="form-text">Pengguna akan dipaksa untuk menukar kata laluan ini semasa log masuk pertama.</div>
            </div>

            <div class="text-end mt-4">
                <a href="admin_users.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
require 'admin_footer.php';
?>
