<?php
// FILE: admin_stock_manual.php (NOW 100% "SLAYED" AND "CLEANSED")
$pageTitle = "Kemaskini Stok";
require 'admin_header.php'; // "Slays" (includes) db.php

// "Boring" (Logic): Get all products for the dropdown
$products_sql = "SELECT ID_produk, nama_produk, stok_semasa FROM PRODUK ORDER BY nama_produk ASC";
$products_result = $conn->query($products_sql);
?>

<div class="main-content">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_dashboard.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Kemaskini Stok </h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                    
                    <form action="admin_stock_manual_process.php" method="POST">
                        
                        <div class="mb-4">
                            <label for="id_produk" class="form-label fw-bold">Nama Item</label>
                            <select class="form-select form-control-lg" id="ID_produk" name="ID_produk" required>
                                <option value="">-- Sila Pilih Item --</option>
                                <?php
                                if ($products_result && $products_result->num_rows > 0) {
                                    while($row = $products_result->fetch_assoc()) {
                                        echo "<option value='{$row['ID_produk']}'>{$row['nama_produk']} (Stok Semasa: {$row['stok_semasa']})</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Tiada produk ditemui. Sila tambah produk dahulu.</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="row mb-4 g-4">
                            <div class="col-md-6">
                                <label for="jumlah_masuk" class="form-label fw-bold">Kuantiti Masuk</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Unit</span>
                                    <input type="number" class="form-control" id="jumlah_masuk" name="jumlah_masuk" value="1" min="1" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="no_dokumen" class="form-label fw-bold">Catatan (Optional)</label>
                                <input type="text" class="form-control form-control-lg" id="no_dokumen" name="no_dokumen" placeholder="Cth: Invois 12345 / Dari Pembekal A">
                                <div class="form-text text-muted">*Boleh diisi untuk rujukan Laporan Transaksi.</div>
                            </div>
                            </div>
                        
                        <input type="hidden" name="ID_staf" value="<?php echo $_SESSION['ID_staf']; ?>">
                        
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

<?php
require 'admin_footer.php';
?>