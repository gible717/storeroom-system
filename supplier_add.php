<?php
// FILE: supplier_add.php (UI Updated)
$pageTitle = "Tambah Pembekal";
require 'admin_header.php';
?>

<div class="d-flex align-items-center mb-4">
    <a href="admin_suppliers.php" class="btn btn-light me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h3 class="mb-0 fw-bold">Tambah Pembekal Baru</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="supplier_add_process.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="id_pembekal" class="form-label">ID Pembekal *</label>
                    <input type="text" class="form-control" id="id_pembekal" name="id_pembekal" 
                        placeholder="Cth: S001" required>
                    <div class="form-text">ID unik untuk pembekal. (Cth: S001)</div>
                </div>
                <div class="col-md-6">
                    <label for="nama_pembekal" class="form-label">Nama Pembekal / Syarikat *</label>
                    <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal" 
                        placeholder="Cth: ABC Supplies Sdn Bhd" required>
                </div>
                <div class="col-12">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                            placeholder="Cth: 123, Jalan Wawasan, 01000 Kangar"></textarea>
                </div>
                <div class="col-md-6">
                    <label for="no_telefon" class="form-label">No. Telefon</label>
                    <input type="text" class="form-control" id="no_telefon" name="no_telefon" 
                        placeholder="Cth: 012-3456789">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                        placeholder="Cth: sales@abc.com">
                </div>
                <div class="col-12 text-end mt-4">
                    <a href="admin_suppliers.php" class="btn btn-light me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pembekal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
require 'admin_footer.php'; 
?>