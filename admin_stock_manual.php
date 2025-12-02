<?php
// admin_stock_manual.php - Manual stock update form

$pageTitle = "Kemaskini Stok";
require 'admin_header.php';

// Get products for dropdown
$products_sql = "SELECT ID_produk, nama_produk, stok_semasa FROM PRODUK ORDER BY nama_produk ASC";
$products_result = $conn->query($products_sql);
?>

<div class="main-content">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_dashboard.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Kemaskini Stok</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                    <form action="admin_stock_manual_process.php" method="POST">
                        <!-- Product Select -->
                        <div class="mb-4">
                            <label for="id_produk" class="form-label fw-bold">Nama Item</label>
                            <select class="form-select form-control-lg" id="ID_produk" name="ID_produk" required onchange="updateStokSemasa()">
                                <option value="">-- Sila Pilih Item --</option>
                                <?php if ($products_result && $products_result->num_rows > 0): ?>
                                    <?php while($row = $products_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['ID_produk']; ?>" data-stok="<?php echo $row['stok_semasa']; ?>"><?php echo $row['nama_produk']; ?></option>
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
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" class="form-control form-control-lg" id="stok_semasa_display" readonly placeholder="-" style="background-color: #f8f9fa; font-weight: bold;">
                                    <span class="input-group-text">Unit</span>
                                </div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="col-md-6">
                                <label for="jumlah_masuk" class="form-label fw-bold">Kuantiti Masuk</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Unit</span>
                                    <input type="number" class="form-control" id="jumlah_masuk" name="jumlah_masuk" value="1" min="1" required>
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
</script>

<?php require 'admin_footer.php'; ?>
