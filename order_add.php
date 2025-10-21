<?php
// FILE: order_add.php (Corrected with UI changes)
$pageTitle = "Tambah Pesanan Baru";
require 'admin_header.php';

// Fetch all suppliers for the dropdown
$suppliers_result = $conn->query("SELECT ID_pembekal, nama_pembekal FROM pembekal ORDER BY nama_pembekal");
if (!$suppliers_result) {
    die("Database Error (Fetching Suppliers): " . $conn->error);
}

// Fetch all products for the dropdown
$products_result = $conn->query("SELECT ID_produk, nama_produk FROM produk ORDER BY nama_produk");
if (!$products_result) {
    die("Database Error (Fetching Products): " . $conn->error);
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="admin_orders.php" class="btn btn-light me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h3 class="mb-0 fw-bold">Tambah Pesanan Baru</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">
        <form action="order_add_process.php" method="POST">
            
            <h5 class="mb-3">Butiran Pesanan</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="id_pembekal" class="form-label">Pembekal *</label>
                    <select class="form-select" id="id_pembekal" name="id_pembekal" required>
                        <option value="" selected disabled>Pilih pembekal...</option>
                        <?php while($row = $suppliers_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['ID_pembekal']; ?>">
                                <?php echo htmlspecialchars($row['nama_pembekal']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tarikh_pesan" class="form-label">Tarikh Pesanan *</label>
                    <input type="date" class="form-control" id="tarikh_pesan" name="tarikh_pesan" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <h5 class="mb-3">Item Pesanan</h5>
            
            <div class="row g-3 align-items-center mb-2">
                <div class="col-md-6">
                    <label class="form-label">Produk *</label>
                    <select class="form-select" name="products[id][]" required>
                        <option value="" selected disabled>Pilih produk...</option>
                        <?php $products_result->data_seek(0); ?>
                        <?php while($row = $products_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['ID_produk']; ?>">
                                <?php echo htmlspecialchars($row['nama_produk']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kuantiti *</label>
                    <input type="number" class="form-control" name="products[qty][]" placeholder="Kuantiti" min="1" required>
                </div>
                </div>
            
            <div class="col-12 text-end mt-4">
                <a href="admin_orders.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
            </div>
        </form>
    </div>
</div>

<?php 
require 'admin_footer.php'; 
?>