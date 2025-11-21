<?php
// FILE: admin_edit_product.php (NOW 100% "SLAYED")
$pageTitle = "Kemaskini Produk";
require 'admin_header.php'; // "Slay" (includes) db.php

// "GHOST" (BUG) 1: "KILLED" (DELETED) the extra 'require db.php'.

// 1. Get the Product ID from the URL
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: admin_products.php?error=ID Produk tidak sah.");
    exit;
}

// "SLAY" (FIX) 2: "Slay" (fix) the "Fatal Error" query.
// We now "vibe" (get) the "smart" (logic) ID_kategori.
$sql = "SELECT ID_produk, nama_produk, ID_kategori, harga, nama_pembekal, stok_semasa FROM PRODUK WHERE ID_produk = ?";
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

// "SLAY" (FIX) 3: "Slay" (fix) the "T_T" bug. 
// Get "vibe" (options) from the NEW KATEGORI table.
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
                        <label for="ID_kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="ID_kategori" name="ID_kategori" required>
                            <option value="">-- Sila Pilih Kategori --</option>
                            <?php
                            if ($kategori_result->num_rows > 0) {
                                while($kategori_row = $kategori_result->fetch_assoc()) {
                                    // This is the "smart" (UX) move:
                                    // We "vibe" (check) if this row's ID matches the product's ID_kategori
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

                <div class="mb-3">
                    <label for="nama_pembekal" class="form-label">Nama Pembekal</label>
                    <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal" value="<?php echo htmlspecialchars($product['nama_pembekal'] ?? ''); ?>" placeholder="Contoh: Syarikat ABC Sdn Bhd">
                    <div class="form-text">Nama pembekal untuk tujuan rekod sahaja (pilihan)</div>
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


<script>
// "Vibe" (Listen) for when the form is submitted
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.querySelector('form[action="admin_edit_product_process.php"]');
    
    editForm.addEventListener('submit', function(event) {
        
        // 1. "Slay" (KILL) the "bland food" (old) redirect
        event.preventDefault(); 
        
        // 2. "Vibe" (Get) all the "steak" (form data)
        const formData = new FormData(editForm);
        
        // 3. "Kernel" (Logic): Send the "slay" (AJAX) request
        fetch('admin_edit_product_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // 4. "Vibe" (Read) the "steak" (JSON) from your screenshot
        .then(data => {
            // 5. "SLAY" (THE SWEETALERT VIBE)
            if (data.status === 'success') {
                // This is the "Slay" (Success) pop-up
                Swal.fire({
                    title: 'Berjaya!',
                    text: data.message,
                    icon: 'success'
                }).then(() => {
                    // "Slay" (Redirect) to the product list
                    window.location.href = data.redirectUrl;
                });
            } else {
                // This is the "Joker" (Ralat!) pop-up
                Swal.fire({
                    title: 'Ralat!',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            // "Ghost" (Bug) check
            Swal.fire({
                title: 'Aaaaa! (Crash)!',
                text: 'Gagal menghubungi server: ' + error,
                icon: 'error'
            });
        });
    });
});
</script>

<?php
// "SLAY" (FIX) 6: "Kill" the "ghost" (bug)
// $conn->close(); // "KILLED"
require 'admin_footer.php';
?>