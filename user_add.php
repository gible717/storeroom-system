<?php
// FILE: user_add.php (Updated with 1-Column Layout)
$pageTitle = "Tambah Pengguna Baru";
require 'admin_header.php';

// Fetch all departments for the dropdown menu
$jabatan_result = $conn->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="admin_users.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Tambah Pengguna Baru</h3>
    </div>
    
    <div></div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem; max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4 p-md-5">
        <form action="user_add_process.php" method="POST">
            
            <div class="mb-3">
                <label for="id_staf" class="form-label">ID Staf <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="id_staf" name="id_staf" 
                    placeholder="Cth: S002" required>
            </div>
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" 
                    placeholder="Cth: Haikal Iman" required>
            </div>
            
            <div class="mb-3">
                <label for="emel" class="form-label">Emel <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="emel" name="emel" 
                    placeholder="Cth: haikal@mpk.gov.my" required>
            </div>

            <div class="mb-3">
                <label for="id_jabatan" class="form-label">Jabatan/Unit <span class="text-danger">*</span></label>
                <select class="form-select" id="id_jabatan" name="id_jabatan" required>
                    <option value="" selected disabled>-- Sila Pilih Jabatan --</option>
                    <?php while($jabatan = $jabatan_result->fetch_assoc()): ?>
                        <option value="<?php echo $jabatan['ID_jabatan']; ?>">
                            <?php echo htmlspecialchars($jabatan['nama_jabatan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="peranan" class="form-label">Peranan <span class="text-danger">*</span></label>
                <select class="form-select" id="peranan" name="peranan" required>
                    <option value="Staf" selected>Staf</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Kata Laluan Sementara <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="password" name="password" 
                    value="User123" readonly>
                <div class="form-text">
                    Pengguna akan dipaksa untuk menukar kata laluan ini semasa log masuk pertama.
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="admin_users.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>