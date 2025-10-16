<?php
// FILE: admin_products.php (Final Version with Status Pills)
$pageTitle = "Pengurusan Produk";
require 'admin_header.php';
require 'db.php'; 

if ($conn === null || $conn->connect_error) {
    die("<div class='container-fluid'><div class='alert alert-danger'>Ralat Sambungan Pangkalan Data.</div></div>");
}

$sql = "SELECT ID_produk, nama_produk, kategori, harga, stok_semasa FROM PRODUK ORDER BY ID_produk ASC";
$result = $conn->query($sql);
?>

<style>
    .btn-icon-only { background-color: transparent; border: none; padding: 0.375rem 0.5rem; font-size: 1.1rem; transition: transform 0.2s ease-in-out; }
    .btn-icon-only:hover { transform: scale(1.2); }
    .text-view { color: #667EEA; }
    .text-edit { color: #64748B; }
    .text-delete { color: #DC2626; }
</style>

<div class="container-fluid">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Senarai Produk Inventori</h1>
        <a href="admin_add_product.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <select class="form-select form-select-sm me-2" style="width: auto;">
                <option selected>Semua Kategori</option>
            </select>
            <select class="form-select form-select-sm" style="width: auto;">
                <option selected>Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
        </div>
        <div class="d-flex align-items-center mt-2 mt-md-0">
            <input type="text" class="form-control form-control-sm" placeholder="Cari..." style="width: 200px;">
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kod Item</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga (RM)</th>
                            <th>Stok</th>
                            <th>Status</th> <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ID_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format((float)$row['harga'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($row['stok_semasa']); ?> unit</td>
                                    <td>
                                        <?php
                                            // This is the logic for the colored status pill
                                            $stok = (int)$row['stok_semasa'];
                                            if ($stok > 10) {
                                                echo '<span class="badge bg-success">In Stock</span>';
                                            } elseif ($stok > 0) {
                                                echo '<span class="badge bg-warning">Low Stock</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Out of Stock</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon-only text-view" title="Lihat"><i class="bi bi-eye-fill"></i></button>
                                        <button class="btn btn-icon-only text-edit" title="Kemaskini"><i class="bi bi-pencil-fill"></i></button>
                                        <button class="btn btn-icon-only text-delete" title="Padam"><i class="bi bi-trash-fill"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">Tiada data produk lagi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing 1 to <?php echo $result ? $result->num_rows : 0; ?> of <?php echo $result ? $result->num_rows : 0; ?> entries</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php
$conn->close(); 
require 'admin_footer.php';
?>