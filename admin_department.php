<?php
// admin_department.php - Department management page

$pageTitle = "Pengurusan Jabatan";
require 'admin_header.php';

// Get all departments
$sql = "SELECT * FROM jabatan ORDER BY nama_jabatan ASC";
$result = $conn->query($sql);
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_users.php" class="btn btn-link nav-link p-0 me-3" title="Kembali ke Pengguna">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h3 class="mb-0 fw-bold">Pengurusan Jabatan</h3>
    </div>
    <div>
        <a href="department_add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Jabatan
        </a>
    </div>
</div>

<!-- Department Table -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Jabatan</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                                <td class="text-end">
                                    <a href="department_edit.php?id=<?php echo $row['ID_jabatan']; ?>" class="btn btn-sm btn-outline-warning" title="Kemaskini">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="department_process.php?action=delete&id=<?php echo $row['ID_jabatan']; ?>"
                                    class="btn btn-sm btn-outline-danger" title="Padam"
                                    onclick="return confirm('Adakah anda pasti mahu memadam jabatan ini?');">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">
                                Tiada jabatan ditemui.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require 'admin_footer.php';
?>
