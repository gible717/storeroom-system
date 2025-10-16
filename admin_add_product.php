<?php
// FILE: admin_add_product.php
$pageTitle = "Tambah Produk Baru";
require 'admin_header.php';
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle; ?></h1>
        <a href="admin_products.php" class="btn btn-secondary btn-icon-split">
            <span class="icon text-white-50">
                <i class="bi bi-arrow-left"></i>
            </span>
            <span class="text">Kembali ke Senarai Produk</span>
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Borang Maklumat Produk</h6>
        </div>
        <div class="card-body">
        <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="admin_add_product_process.php" method="POST">
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
                        <input type="text" class="form-control" id="kategori" name="kategori">
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
require 'admin_footer.php';
?>