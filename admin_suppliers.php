<?php
// FILE: admin_suppliers.php
$pageTitle = "Pengurusan Pembekal";
require 'admin_header.php';

// Fetch all suppliers from the database
$sql = "SELECT ID_pembekal, nama_pembekal, alamat, no_telefon, email 
        FROM pembekal 
        ORDER BY nama_pembekal ASC";
$suppliers_result = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Pengurusan Pembekal</h3>
    <a href="supplier_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Tambah Pembekal
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

        <div class="row mb-3">
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-0" id="searchInput" placeholder="Cari Pembekal...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="suppliersTable">
                <thead>
                    <tr>
                        <th>ID Pembekal</th>
                        <th>Nama Pembekal</th>
                        <th>Alamat</th>
                        <th>No. Telefon</th>
                        <th>Email</th>
                        <th class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($suppliers_result && $suppliers_result->num_rows > 0): ?>
                        <?php while ($row = $suppliers_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ID_pembekal']); ?></td>
                                <td class="search-field"><?php echo htmlspecialchars($row['nama_pembekal']); ?></td>
                                <td class="search-field"><?php echo htmlspecialchars($row['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_telefon']); ?></td>
                                <td class="search-field"><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="supplier_edit.php?id=<?php echo $row['ID_pembekal']; ?>" class="btn btn-warning btn-sm" title="Kemaskini">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="supplier_delete.php?id=<?php echo $row['ID_pembekal']; ?>" class="btn btn-danger btn-sm" title="Padam" onclick="return confirm('Anda pasti mahu padam pembekal ini?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Tiada pembekal ditemui.</td></tr>
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