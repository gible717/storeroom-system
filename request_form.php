<?php
// FILE: request_form.php
require 'auth_check.php'; // Protects page and includes db.php

// NEW: Fetch all products from the database that are in stock to populate the dropdown
$products_result = $conn->query("SELECT ID_produk, nama_produk, stok_semasa FROM produk WHERE stok_semasa > 0 ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Permohonan Stok - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.8rem 1rem; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .form-control:read-only { background-color: #e9ecef; cursor: not-allowed; }
        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; }
        .btn-light { border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="position-relative text-center mb-4">
                    <a href="staff_dashboard.php" class="position-absolute start-0 text-dark" title="Kembali ke Dashboard"><i class="bi bi-arrow-left fs-4"></i></a>
                    <h3 class="mb-0">Borang Permohonan Stok</h3>
                </div>

                <div class="card form-card">
                    <div class="card-body p-5">
                        <form action="request_form_process.php" method="POST">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="nama_staf" class="form-label">*Nama Staf</label>
                                    <input type="text" class="form-control" id="nama_staf" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                                </div>
                                
                                <div class="col-12">
                                    <label for="id_produk" class="form-label">*Nama Produk</label>
                                    <select class="form-select" id="id_produk" name="id_produk" required>
                                        <option value="" selected disabled>Pilih produk...</option>
                                        <?php while ($product = $products_result->fetch_assoc()): ?>
                                            <option value="<?php echo $product['ID_produk']; ?>" data-stok="<?php echo (int)$product['stok_semasa']; ?>">
                                                <?php echo htmlspecialchars($product['nama_produk']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="jumlah_diminta" class="form-label">*Kuantiti Diminta</label>
                                    <input type="number" class="form-control" id="jumlah_diminta" name="jumlah_diminta" placeholder="Masukkan Kuantiti" required min="1">
                                </div>
                                <div class="col-md-6">
                                    <label for="stok_sedia_ada" class="form-label">Stok Sedia Ada</label>
                                    <input type="text" class="form-control" id="stok_sedia_ada" placeholder="Contoh: 25 unit" readonly>
                                </div>

                                <div class="col-12">
                                    <label for="jabatan_unit" class="form-label">*Jabatan/Unit</label>
                                    <input type="text" class="form-control" id="jabatan_unit" name="jabatan_unit" placeholder="Contoh: Unit Teknologi Maklumat" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="no_bpsi" class="form-label">No. BPSI</label>
                                    <input type="text" class="form-control" id="no_bpsi" name="no_bpsi" placeholder="Masukkan nombor rujukan (optional)">
                                </div>
                                
                                <div class="col-12">
                                    <label for="catatan" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan..."></textarea>
                                </div>
                                
                                <div class="col-12 text-end">
                                    <a href="staff_dashboard.php" class="btn btn-light">Batal</a>
                                    <button type="submit" class="btn btn-primary">Hantar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // NEW: This JavaScript runs when the user picks a product from the dropdown.
        document.getElementById('id_produk').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const stockLevel = selectedOption.getAttribute('data-stok'); // Get stock from the 'data-stok' attribute
            const stockDisplay = document.getElementById('stok_sedia_ada');
            const quantityInput = document.getElementById('jumlah_diminta');

            if (stockLevel) {
                stockDisplay.value = stockLevel + ' unit';
                quantityInput.setAttribute('max', stockLevel); // Set max quantity to prevent over-requesting
            } else {
                stockDisplay.value = '';
                quantityInput.removeAttribute('max');
            }
        });
    </script>
</body>
</html>