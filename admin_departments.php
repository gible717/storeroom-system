<?php
// FILE: admin_departments.php (New Full-Width Version)
$pageTitle = "Pengurusan Jabatan";
require 'admin_header.php';

// --- Fetch All Departments for the List ---
$departments = $conn->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="admin_users.php" class="btn btn-light" title="Kembali ke Pengguna">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Pengurusan Jabatan</h3>
    </div>
    
    <div>
        <a href="department_add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Jabatan Baru
        </a>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Senarai Jabatan Sedia Ada</h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Jabatan</th>
                        <th>Nama Jabatan</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($departments && $departments->num_rows > 0): ?>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $dept['ID_jabatan']; ?></td>
                                <td><?php echo htmlspecialchars($dept['nama_jabatan']); ?></td>
                                <td class="text-end">
                                    <a href="department_edit.php?id=<?php echo $dept['ID_jabatan']; ?>" 
                                    class="btn btn-sm btn-outline-warning" title="Kemaskini">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="department_process.php?action=delete&id=<?php echo $dept['ID_jabatan']; ?>" 
                                    class="btn btn-sm btn-outline-danger" title="Padam"
                                    onclick="return confirm('Adakah anda pasti mahu memadam jabatan ini?');">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Tiada jabatan ditemui. Sila tambah jabatan baru.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>