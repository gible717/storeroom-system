<?php
// admin_category.php - Category management page

$pageTitle = "Pengurusan Kategori";
require 'admin_header.php';

// Get all categories
$kategori_list_sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$kategori_list_result = $conn->query($kategori_list_sql);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Pengurusan Kategori</h1>
    </div>

    <div class="row">
        <!-- Category List -->
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white py-3" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <h6 class="m-0 fw-bold text-primary">Senarai Kategori Sedia Ada</h6>
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
                                <?php if ($kategori_list_result && $kategori_list_result->num_rows > 0): ?>
                                    <?php while ($row = $kategori_list_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="align-middle ps-4"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="editCategory(<?php echo $row['ID_kategori']; ?>, '<?php echo addslashes($row['nama_kategori']); ?>')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <form action="admin_category_process.php" method="POST" class="d-inline" onsubmit="return confirm('Anda pasti mahu padam kategori ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ID_kategori" value="<?php echo $row['ID_kategori']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
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
                    <span id="form-title">Tambah Kategori Baru</span>
                </div>
                <div class="card-body p-4">
                    <form action="admin_category_process.php" method="POST" id="categoryForm">
                        <input type="hidden" name="action" id="form-action" value="add">
                        <input type="hidden" name="ID_kategori" id="form-id" value="">
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Cth: Toner" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="form-btn">
                            <i class="bi bi-plus-circle-fill" id="form-icon"></i> <span id="form-btn-text">Tambah</span>
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mt-2 d-none" id="cancel-btn" onclick="resetForm()">
                            Batal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Edit category function
function editCategory(id, nama) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = id;
    document.getElementById('nama_kategori').value = nama;
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
    document.getElementById('form-title').textContent = 'Tambah Kategori Baru';
    document.getElementById('form-icon').className = 'bi bi-plus-circle-fill';
    document.getElementById('form-btn-text').textContent = 'Tambah';
    document.getElementById('cancel-btn').classList.add('d-none');
}
</script>

<?php require 'admin_footer.php'; ?>
