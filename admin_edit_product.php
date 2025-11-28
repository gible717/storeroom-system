<?php
// admin_edit_product.php - Form to edit existing product

$pageTitle = "Kemaskini Produk";
require 'admin_header.php';

// Get product ID from URL
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: admin_products.php?error=ID Produk tidak sah.");
    exit;
}

// Fetch product data
$sql = "SELECT ID_produk, nama_produk, ID_kategori, harga, nama_pembekal, stok_semasa FROM PRODUK WHERE ID_produk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: admin_products.php?error=Produk tidak ditemui.");
    exit;
}

// Get categories for dropdown
$kategori_result = $conn->query("SELECT * FROM KATEGORI ORDER BY nama_kategori ASC");
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle; ?>: <?php echo htmlspecialchars($product['nama_produk']); ?></h1>
    </div>

    <div class="card shadow mb-4 border-0" style="border-radius: 1rem;">
        <div class="card-body p-4 p-md-5">
            <form action="admin_edit_product_process.php" method="POST">
                <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($product['ID_produk']); ?>">

                <!-- Product Name -->
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>

                <!-- Product ID (read-only) & Category -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_produk_display" class="form-label">ID Produk / SKU</label>
                        <input type="text" class="form-control" id="id_produk_display" value="<?php echo htmlspecialchars($product['ID_produk']); ?>" disabled>
                        <div class="form-text">ID Produk tidak boleh diubah.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="ID_kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="ID_kategori" name="ID_kategori" required>
                            <option value="">-- Sila Pilih Kategori --</option>
                            <?php
                            if ($kategori_result->num_rows > 0) {
                                while($kategori_row = $kategori_result->fetch_assoc()) {
                                    $selected = ($product['ID_kategori'] == $kategori_row['ID_kategori']) ? 'selected' : '';
                                    echo "<option value='{$kategori_row['ID_kategori']}' $selected>{$kategori_row['nama_kategori']}</option>";
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
                    <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal" value="<?php echo htmlspecialchars($product['nama_pembekal'] ?? ''); ?>" placeholder="Contoh: Syarikat ABC Sdn Bhd">
                    <div class="form-text">Nama pembekal untuk tujuan rekod sahaja (pilihan)</div>
                </div>

                <!-- Price & Stock -->
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

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end pt-3 mt-3 border-top">
                    <a href="admin_products.php" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-success">Kemaskini Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle form submit via AJAX for better UX
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.querySelector('form[action="admin_edit_product_process.php"]');

    editForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(editForm);

        fetch('admin_edit_product_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Berjaya!',
                    text: data.message,
                    icon: 'success'
                }).then(() => {
                    window.location.href = data.redirectUrl;
                });
            } else {
                Swal.fire({
                    title: 'Ralat!',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Ralat Sambungan!',
                text: 'Gagal menghubungi server: ' + error,
                icon: 'error'
            });
        });
    });
});
</script>

<?php require 'admin_footer.php'; ?>
