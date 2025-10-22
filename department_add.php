<?php
// FILE: department_add.php
$pageTitle = "Tambah Jabatan";
require 'admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="admin_departments.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Tambah Jabatan Baru</h3>
    </div>
    
    <div></div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem; max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4 p-md-5">
        <form action="department_process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" 
                    placeholder="Cth: Jabatan Kewangan" required>
            </div>
            
            <div class="text-end mt-4">
                <a href="admin_departments.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php 
require 'admin_footer.php'; 
?>