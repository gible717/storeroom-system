<?php
// FILE: admin_edit_product.php
$pageTitle = "Kemaskini Produk";
require 'admin_header.php';
require 'db.php';

// 1. Get the Product ID from the URL
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    // Redirect if no ID is provided
    header("Location: admin_products.php?error=ID Produk tidak sah.");
    exit;
}

// 2. Fetch the product's current data from the database
$sql = "SELECT ID_produk, nama_produk, kategori, harga, stok_semasa FROM PRODUK WHERE ID_produk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Redirect if product with that ID doesn't exist
if (!$product) {
    header("Location: admin_products.php?error=Produk tidak ditemui.");
    exit;
}

// Fetch unique categories for the datalist
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM PRODUK WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle; ?>: <?php echo htmlspecialchars($product['nama_produk']); ?></h1>
    </div>

    <div class="card shadow mb-4">
        
        <div class="card-body">
            <form action="admin_edit_product_process.php" method="POST">
                <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($product['ID_produk']); ?>">

                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_produk_display" class="form-label">ID Produk / SKU</label>
                        <input type="text" class="form-control" id="id_produk_display" value="<?php echo htmlspecialchars($product['ID_produk']); ?>" disabled>
                        <div class="form-text">ID Produk tidak boleh diubah.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="kategori" class="form-label">Kategori</label>
                        <input class="form-control" list="kategoriOptions" id="kategori" name="kategori" value="<?php echo htmlspecialchars($product['kategori']); ?>" placeholder="Taip atau pilih kategori...">
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
                            <input type="number" class="form-control" id="harga" name="harga" value="<?php echo htmlspecialchars($product['harga']); ?>" step="0.01" min="0.00" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="stok_semasa" class="form-label">Kuantiti Stok Semasa</label>
                        <input type="number" class="form-control" id="stok_semasa" name="stok_semasa" value="<?php echo htmlspecialchars($product['stok_semasa']); ?>" min="0" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 mt-3 border-top">
                    <a href="admin_products.php" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-success">Kemaskini Produk</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require 'admin_footer.php';
?>