<?php
// FILE: request_form.php (Refined to match your design)
$pageTitle = "Borang Permohonan Stok";
require 'staff_header.php'; // This includes security, CSS, and the navbar

// --- START: FETCH STAFF DEPARTMENT ---
// We must fetch the department name, as it's not stored in the session.
$nama_jabatan = 'Jabatan Tidak Ditemui'; // Default value
$staff_id = $_SESSION['ID_staf'] ?? null;

if ($staff_id) {
    $stmt_dept = $conn->prepare("SELECT j.nama_jabatan
                                FROM staf s
                                LEFT JOIN jabatan j ON s.ID_jabatan = j.ID_jabatan
                                WHERE s.ID_staf = ?");
    if ($stmt_dept) {
        $stmt_dept->bind_param("s", $staff_id);
        $stmt_dept->execute();
        $dept_result = $stmt_dept->get_result();
        if ($dept_row = $dept_result->fetch_assoc()) {
            // Check if nama_jabatan is not null or empty
            if (!empty($dept_row['nama_jabatan'])) {
                $nama_jabatan = $dept_row['nama_jabatan'];
            }
        }
        $stmt_dept->close();
    }
}
// --- END: FETCH STAFF DEPARTMENT ---

// Fetch products for the dropdown (existing code)
$products_result = $conn->query("SELECT ID_produk, nama_produk, stok_semasa FROM produk WHERE stok_semasa > 0 ORDER BY nama_produk ASC");
?>

<style>
    .form-card {
        background: #ffffff;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
    }
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.8rem 1rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .form-control:read-only {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    .btn-primary {
        background-color: #4f46e5;
        border-color: #4f46e5;
        border-radius: 0.5rem;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
    }
    .btn-light {
        border-radius: 0.5rem;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        border: 1px solid #dee2e6;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="position-relative text-center mb-4">
            <a href="staff_dashboard.php" class="position-absolute top-50 start-0 translate-middle-y text-dark" title="Kembali">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h3 class="mb-0 fw-bold">Borang Permohonan Stok</h3>
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
    <label for="jabatan_unit_display" class="form-label">Jabatan/Unit</label> 
    <input 
        type="text" 
        class="form-control" 
        id="jabatan_unit_display" 
        value="<?php echo htmlspecialchars($nama_jabatan); ?>" 
        readonly 
    >
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

<script>
    // This JavaScript for updating stock stays here
    document.getElementById('id_produk').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockLevel = selectedOption.getAttribute('data-stok');
        const stockDisplay = document.getElementById('stok_sedia_ada');
        const quantityInput = document.getElementById('jumlah_diminta');

        if (stockLevel) {
            stockDisplay.value = stockLevel + ' unit';
            quantityInput.setAttribute('max', stockLevel);
        } else {
            stockDisplay.value = '';
            quantityInput.removeAttribute('max');
        }
    });
</script>

<?php 
$conn->close();
require 'staff_footer.php'; 
?>