<?php
// admin_add_product.php - Form to add new product

$pageTitle = "Tambah Produk";
require 'admin_header.php';

// Get categories for dropdown
$kategori_sql = "SELECT * FROM KATEGORI ORDER BY nama_kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Get error message and form data from session/query
$error = isset($_GET['error']) ? $_GET['error'] : null;
$error_field = isset($_SESSION['error_field']) ? $_SESSION['error_field'] : null;
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear session data after retrieving
unset($_SESSION['error_field']);
unset($_SESSION['form_data']);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Tambah Produk Baru</h1>
    </div>

    <!-- Error Alert -->
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4 border-0" style="border-radius: 1rem;">
        <div class="card-body p-4 p-md-5">
            <form action="admin_add_product_process.php" method="POST">

                <!-- Product Name -->
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk"
                        value="<?php echo isset($form_data['nama_produk']) ? htmlspecialchars($form_data['nama_produk']) : ''; ?>"
                        required>
                </div>

                <!-- Product ID & Category -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_produk" class="form-label">ID Produk / SKU <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo ($error_field === 'id_produk') ? 'is-invalid' : ''; ?>"
                            id="id_produk" name="id_produk"
                            value="<?php echo isset($form_data['id_produk']) ? htmlspecialchars($form_data['id_produk']) : ''; ?>"
                            required>
                        <div class="form-text">Kod unik untuk produk ini. Contoh: A4-PAPER-001</div>
                        <?php if ($error_field === 'id_produk'): ?>
                        <div class="invalid-feedback">ID Produk ini sudah wujud. Sila gunakan ID yang lain.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="ID_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="ID_kategori" name="ID_kategori" required>
                            <option value="">-- Sila Pilih Kategori --</option>
                            <?php
                            if ($kategori_result->num_rows > 0) {
                                while($row = $kategori_result->fetch_assoc()) {
                                    $selected = (isset($form_data['ID_kategori']) && $form_data['ID_kategori'] == $row['ID_kategori']) ? 'selected' : '';
                                    echo "<option value='{$row['ID_kategori']}' $selected>{$row['nama_kategori']}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>Tiada kategori. Sila 'Urus Kategori' dahulu.</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Supplier Name (optional, for record only) -->
                <div class="mb-3">
                    <label for="nama_pembekal" class="form-label">Nama Pembekal</label>
                    <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal"
                        placeholder="Contoh: Syarikat ABC Sdn Bhd"
                        value="<?php echo isset($form_data['nama_pembekal']) ? htmlspecialchars($form_data['nama_pembekal']) : ''; ?>">
                    <div class="form-text">Nama pembekal untuk tujuan rekod sahaja (pilihan)</div>
                </div>

                <!-- Price & Stock -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="harga" class="form-label">Harga Seunit (RM)</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" id="harga" name="harga" step="0.01" min="0.00"
                                placeholder="0.00"
                                value="<?php echo isset($form_data['harga']) ? htmlspecialchars($form_data['harga']) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="stok_semasa" class="form-label">Kuantiti Stok Awal <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stok_semasa" name="stok_semasa"
                            value="<?php echo isset($form_data['stok_semasa']) ? htmlspecialchars($form_data['stok_semasa']) : '0'; ?>"
                            min="0" required>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end pt-3 mt-4 border-top">
                    <a href="admin_products.php" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
