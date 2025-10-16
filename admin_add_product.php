<?php
// FILE: admin_add_product.php (with upgraded Category input)
$pageTitle = "Tambah Produk Baru";
require 'admin_header.php';
require 'db.php'; // We need the database connection to get categories

// Fetch unique categories for the datalist
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM PRODUK WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Borang Maklumat Produk</h6>
        </div>
        <div class="card-body">
            <form action="admin_add_product_process.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_produk" class="form-label">ID Produk / SKU</label>
                        <input type="text" class="form-control" id="id_produk" name="id_produk" required>
                        <div class="form-text">Kod unik untuk produk ini. Contoh: A4-PAPER-001</div>
                    </div>
                    <div class="col-md-6">
                        <label for="kategori" class="form-label">Kategori</label>
                        <input class="form-control" list="kategoriOptions" id="kategori" name="kategori" placeholder="Taip atau pilih kategori...">
                        <datalist id="kategoriOptions">
                            <?php while($kategori_row = $kategori_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($kategori_row['kategori']); ?>">
                            <?php endwhile; ?>
                        </datalist>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="harga" class="form-label">Harga Seunit (RM)</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control" id="harga" name="harga" step="0.01" min="0.00" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="stok_semasa" class="form-label">Kuantiti Stok Awal</label>
                        <input type="number" class="form-control" id="stok_semasa" name="stok_semasa" value="0" min="0" required>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'admin_footer.php';
?>