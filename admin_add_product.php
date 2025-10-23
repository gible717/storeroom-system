<?php
// FILE: admin_add_product.php (Layout Tweak)
$pageTitle = "Tambah Produk";
require 'admin_header.php';
require 'db.php'; 

// Fetch unique categories for the datalist
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM PRODUK WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Tambah Produk Baru</h1>
    </div>

    <div class="card shadow mb-4 border-0" style="border-radius: 1rem;">
        <div class="card-body p-4 p-md-5">
            
            <form action="admin_add_product_process.php" method="POST">

                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                </div>

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

                <div class="d-flex justify-content-end pt-3 mt-4 border-top">
                    <a href="admin_products.php" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'admin_footer.php';
?>