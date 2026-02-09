<?php
// admin_stock_manual.php - Manual stock update form

$pageTitle = "Pengemaskinian Stok";
require 'admin_header.php';

// Get main categories for dropdown (with subcategory IDs for filtering)
$main_kategori_result = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI WHERE parent_id IS NULL ORDER BY nama_kategori ASC");
$kategori_with_subs = [];
if ($main_kategori_result) {
    while ($m = $main_kategori_result->fetch_assoc()) {
        $all_ids = [$m['ID_kategori']];
        $sub_stmt = $conn->prepare("SELECT ID_kategori FROM KATEGORI WHERE parent_id = ?");
        $sub_stmt->bind_param("i", $m['ID_kategori']);
        $sub_stmt->execute();
        $sub_result = $sub_stmt->get_result();
        while ($s = $sub_result->fetch_assoc()) {
            $all_ids[] = $s['ID_kategori'];
        }
        $m['all_ids'] = implode(',', $all_ids);
        $kategori_with_subs[] = $m;
    }
}

// Get products for dropdown from barang table
$products_sql = "SELECT no_kod AS ID_produk, perihal_stok AS nama_produk, baki_semasa AS stok_semasa, ID_kategori FROM barang ORDER BY perihal_stok ASC";
$products_result = $conn->query($products_sql);
?>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center mb-4">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Kemaskini Stok</h1>
            </div>
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                    <form action="admin_stock_manual_process.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <!-- Category Filter -->
                        <div class="mb-4">
                            <label for="kategori_filter" class="form-label fw-bold">Kategori</label>
                            <select class="form-select form-control-lg" id="kategori_filter" onchange="filterProducts()">
                                <option value="" data-all-ids="">-- Semua Kategori --</option>
                                <?php foreach ($kategori_with_subs as $kat): ?>
                                    <option value="<?php echo $kat['ID_kategori']; ?>" data-all-ids="<?php echo $kat['all_ids']; ?>">
                                        <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Product Select -->
                        <div class="mb-4">
                            <label for="id_produk" class="form-label fw-bold">Nama Item <span class="text-danger">*</span></label>
                            <select class="form-select form-control-lg" id="ID_produk" name="ID_produk" required onchange="updateStokSemasa()">
                                <option value="">-- Sila Pilih Item --</option>
                                <?php if ($products_result && $products_result->num_rows > 0): ?>
                                    <?php
                                    $products_result->data_seek(0); // Reset pointer
                                    while($row = $products_result->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $row['ID_produk']; ?>" data-stok="<?php echo $row['stok_semasa']; ?>" data-kategori="<?php echo $row['ID_kategori']; ?>"><?php echo htmlspecialchars($row['nama_produk']); ?></option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tiada produk ditemui. Sila tambah produk dahulu.</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Stock and Quantity Row -->
                        <div class="row mb-4 g-4">
                            <!-- Current Stock Display -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Stok Semasa</label>
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control form-control-lg" id="stok_semasa_display" readonly placeholder="-" style="background-color: #f8f9fa; font-weight: bold;">
                                    <span class="input-group-text">Unit</span>
                                </div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="col-md-6">
                                <label for="jumlah_masuk" class="form-label fw-bold">Kuantiti Masuk <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <input type="number" class="form-control" id="jumlah_masuk" name="jumlah_masuk" value="1" min="1" required>
                                    <span class="input-group-text">Unit</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes (Full Width) -->
                        <div class="mb-4">
                            <label for="no_dokumen" class="form-label fw-bold">Catatan (Optional)</label>
                            <input type="text" class="form-control form-control-lg" id="no_dokumen" name="no_dokumen" placeholder="Cth: Invois 12345 / Dari Pembekal A">
                            <div class="form-text text-muted">*Boleh diisi untuk rujukan Laporan Transaksi.</div>
                        </div>

                        <input type="hidden" name="ID_staf" value="<?php echo $_SESSION['ID_staf']; ?>">

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end mt-5">
                            <a href="admin_dashboard.php" class="btn btn-secondary me-3">Batal</a>
                            <button type="submit" class="btn btn-primary">Tambah Stok</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStokSemasa() {
    const selectElement = document.getElementById('ID_produk');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const stokSemasa = selectedOption.getAttribute('data-stok');
    const displayField = document.getElementById('stok_semasa_display');

    if (stokSemasa && selectElement.value !== '') {
        displayField.value = stokSemasa;
    } else {
        displayField.value = '';
    }
}

function filterProducts() {
    const kategoriFilter = document.getElementById('kategori_filter');
    const selectedOption = kategoriFilter.options[kategoriFilter.selectedIndex];
    const allIdsStr = selectedOption.getAttribute('data-all-ids') || '';
    const allIds = allIdsStr ? allIdsStr.split(',') : [];
    const productSelect = document.getElementById('ID_produk');
    const options = productSelect.querySelectorAll('option');

    // Reset product selection
    productSelect.value = '';
    document.getElementById('stok_semasa_display').value = '';

    // Show/hide options based on category filter (matches main + all subcategories)
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }

        const productKategori = option.getAttribute('data-kategori');

        if (allIds.length === 0 || allIds.includes(productKategori)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}
</script>

<?php require 'admin_footer.php'; ?>
