<?php
// admin_category.php - Category management page with subcategory support

$pageTitle = "Pengurusan Kategori";
require 'admin_header.php';

// Get all categories with parent info
$kategori_list_sql = "
    SELECT k.*, p.nama_kategori AS parent_name
    FROM KATEGORI k
    LEFT JOIN KATEGORI p ON k.parent_id = p.ID_kategori
    ORDER BY COALESCE(k.parent_id, k.ID_kategori), k.parent_id IS NOT NULL, k.nama_kategori ASC
";
$kategori_list_result = $conn->query($kategori_list_sql);

// Organize into tree structure
$main_categories = [];
$sub_categories = [];
if ($kategori_list_result && $kategori_list_result->num_rows > 0) {
    while ($row = $kategori_list_result->fetch_assoc()) {
        if ($row['parent_id'] === null) {
            $main_categories[$row['ID_kategori']] = $row;
        } else {
            $sub_categories[$row['parent_id']][] = $row;
        }
    }
}

// Get main categories for parent selector dropdown
$main_for_select = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI WHERE parent_id IS NULL ORDER BY nama_kategori ASC");
$main_options = [];
while ($m = $main_for_select->fetch_assoc()) {
    $main_options[] = $m;
}

// Get error/success message and form data from session/query
$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error_field = isset($_SESSION['error_field']) ? $_SESSION['error_field'] : null;
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$edit_mode = isset($_SESSION['edit_mode']) ? $_SESSION['edit_mode'] : false;

// Clear session data after retrieving
unset($_SESSION['error_field']);
unset($_SESSION['form_data']);
unset($_SESSION['edit_mode']);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Pengurusan Kategori</h1>
    </div>

    <!-- Error Alert -->
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Success Alert -->
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Category List -->
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white py-3" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <h6 class="m-0 fw-bold text">Senarai Kategori Sedia Ada</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Nama Kategori</th>
                                    <th scope="col" class="text-end pe-4">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($main_categories)): ?>
                                    <?php foreach ($main_categories as $main): ?>
                                    <tr>
                                        <td class="align-middle ps-4">
                                            <strong><?php echo htmlspecialchars($main['nama_kategori']); ?></strong>
                                            <?php if (isset($sub_categories[$main['ID_kategori']])): ?>
                                                <span class="badge bg-light text-muted ms-2"><?php echo count($sub_categories[$main['ID_kategori']]); ?> subkategori</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="editCategory(<?php echo $main['ID_kategori']; ?>, '<?php echo addslashes($main['nama_kategori']); ?>', '')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <form action="admin_category_process.php" method="POST" class="d-inline delete-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ID_kategori" value="<?php echo $main['ID_kategori']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php if (isset($sub_categories[$main['ID_kategori']])): ?>
                                        <?php foreach ($sub_categories[$main['ID_kategori']] as $sub): ?>
                                        <tr>
                                            <td class="align-middle ps-4" style="padding-left: 2.5rem !important; border-left: 3px solid #4f46e5;">
                                                <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                                <?php echo htmlspecialchars($sub['nama_kategori']); ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="editCategory(<?php echo $sub['ID_kategori']; ?>, '<?php echo addslashes($sub['nama_kategori']); ?>', '<?php echo $main['ID_kategori']; ?>')">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <form action="admin_category_process.php" method="POST" class="d-inline delete-form">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="ID_kategori" value="<?php echo $sub['ID_kategori']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan='2' class='text-center p-4'>Tiada kategori ditemui. Sila tambah kategori baru.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Form -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <span id="form-title"><?php echo $edit_mode ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></span>
                </div>
                <div class="card-body p-4">
                    <form action="admin_category_process.php" method="POST" id="categoryForm">
                        <input type="hidden" name="action" id="form-action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                        <input type="hidden" name="ID_kategori" id="form-id" value="<?php echo isset($form_data['ID_kategori']) ? htmlspecialchars($form_data['ID_kategori']) : ''; ?>">
                        <div class="mb-3">
                            <label for="parent_id" class="form-label fw-bold">Jenis Kategori</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">Kategori Utama</option>
                                <?php foreach ($main_options as $m): ?>
                                    <option value="<?php echo $m['ID_kategori']; ?>"
                                        <?php echo (isset($form_data['parent_id']) && $form_data['parent_id'] == $m['ID_kategori']) ? 'selected' : ''; ?>>
                                        Subkategori bagi: <?php echo htmlspecialchars($m['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Biarkan "Kategori Utama" jika ini bukan subkategori.</small>
                        </div>
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control <?php echo ($error_field === 'nama_kategori') ? 'is-invalid' : ''; ?>"
                                id="nama_kategori" name="nama_kategori" placeholder="Cth: Toner / Canon"
                                value="<?php echo isset($form_data['nama_kategori']) ? htmlspecialchars($form_data['nama_kategori']) : ''; ?>"
                                required>
                            <?php if ($error_field === 'nama_kategori'): ?>
                            <div class="invalid-feedback">Nama kategori ini sudah wujud. Sila gunakan nama yang lain.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="form-btn">
                            <i class="bi <?php echo $edit_mode ? 'bi-check-circle-fill' : 'bi-plus-circle-fill'; ?>" id="form-icon"></i> <span id="form-btn-text"><?php echo $edit_mode ? 'Kemaskini' : 'Tambah'; ?></span>
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mt-2 <?php echo $edit_mode ? '' : 'd-none'; ?>" id="cancel-btn" onclick="resetForm()">
                            Batal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Delete confirmation using SweetAlert
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Padam Kategori?',
            text: 'Adakah anda pasti mahu padam kategori ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Padam!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Edit category function
function editCategory(id, nama, parentId) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = id;
    document.getElementById('nama_kategori').value = nama;
    document.getElementById('parent_id').value = parentId || '';
    document.getElementById('form-title').textContent = 'Edit Kategori';
    document.getElementById('form-icon').className = 'bi bi-check-circle-fill';
    document.getElementById('form-btn-text').textContent = 'Kemaskini';
    document.getElementById('cancel-btn').classList.remove('d-none');

    // Scroll to form
    document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth' });
}

// Reset form to add mode
function resetForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('nama_kategori').value = '';
    document.getElementById('parent_id').value = '';
    document.getElementById('form-title').textContent = 'Tambah Kategori Baru';
    document.getElementById('form-icon').className = 'bi bi-plus-circle-fill';
    document.getElementById('form-btn-text').textContent = 'Tambah';
    document.getElementById('cancel-btn').classList.add('d-none');
}
</script>

<?php require 'admin_footer.php'; ?>
