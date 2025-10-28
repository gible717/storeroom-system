<?php
// FILE: admin_add_product.php (NOW 100% "SLAYED")
$pageTitle = "Tambah Produk";
require 'admin_header.php'; // This "slays" (includes) db.php

// "GHOST" (BUG) 1: "KILLED" (DELETED) the extra 'require db.php'.

// START: "SLAY" (STRATEGIST) FIX
// We now "vibe" (get) the list from your NEW KATEGORI table
$kategori_sql = "SELECT * FROM KATEGORI ORDER BY nama_kategori ASC";
$kategori_result = $conn->query($kategori_sql);
// END: "SLAY" FIX
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
                        <label for="ID_kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="ID_kategori" name="ID_kategori" required>
                            <option value="">-- Sila Pilih Kategori --</option>
                            <?php
                            if ($kategori_result->num_rows > 0) {
                                while($row = $kategori_result->fetch_assoc()) {
                                    echo "<option value='{$row['ID_kategori']}'>{$row['nama_kategori']}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>Tiada kategori. Sila 'Urus Kategori' dahulu.</option>";
                            }
                            ?>
                        </select>
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
// "GHOST" (BUG) 3: "KILLED" (DELETED) the extra '$conn->close();'
require 'admin_footer.php';
?>