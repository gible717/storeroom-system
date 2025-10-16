<?php
// FILE: admin_products.php (with Toast Notification)
$pageTitle = "Pengurusan Produk";
require 'admin_header.php';
require 'db.php';

if ($conn === null || $conn->connect_error) {
    die("<div class='container-fluid'><div class='alert alert-danger'>Ralat Sambungan Pangkalan Data.</div></div>");
}

// --- Filter Logic Starts Here ---
$where_clauses = [];
$params = [];
$types = '';

// Status Filter
$status_filter = $_GET['status'] ?? '';
if ($status_filter === 'in_stock') {
    $where_clauses[] = "stok_semasa > 10";
} elseif ($status_filter === 'low_stock') {
    $where_clauses[] = "stok_semasa > 0 AND stok_semasa <= 10";
} elseif ($status_filter === 'out_of_stock') {
    $where_clauses[] = "stok_semasa = 0";
}

// Category Filter
$category_filter = $_GET['kategori'] ?? '';
if (!empty($category_filter)) {
    $where_clauses[] = "kategori = ?";
    $params[] = $category_filter;
    $types .= 's';
}

// Build the SQL query
$sql = "SELECT ID_produk, nama_produk, kategori, harga, stok_semasa FROM PRODUK";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY ID_produk ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch unique categories for the dropdown
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM PRODUK WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");

?>

<style>
    .btn-icon-only { background-color: transparent; border: none; padding: 0.375rem 0.5rem; font-size: 1.1rem; transition: transform 0.2s ease-in-out; }
    .btn-icon-only:hover { transform: scale(1.2); }
    .text-view { color: #667EEA; }
    .text-edit { color: #64748B; }
    .text-delete { color: #DC2626; }
</style>

<div class="container-fluid">

    <div class="toast-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Senarai Produk Inventori</h1>
        <a href="admin_add_product.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
    </div>

    <form action="admin_products.php" method="GET" id="filterForm">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <select name="kategori" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Semua Kategori</option>
                    <?php while($kategori_row = $kategori_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($kategori_row['kategori']); ?>" <?php if ($category_filter === $kategori_row['kategori']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kategori_row['kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Status</option>
                    <option value="in_stock" <?php if ($status_filter === 'in_stock') echo 'selected'; ?>>In Stock</option>
                    <option value="low_stock" <?php if ($status_filter === 'low_stock') echo 'selected'; ?>>Low Stock</option>
                    <option value="out_of_stock" <?php if ($status_filter === 'out_of_stock') echo 'selected'; ?>>Out of Stock</option>
                </select>
            </div>
            </div>
    </form>

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
                            <th>Status</th>
                            <th>Tindakan</th>
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
                                        <a href="admin_edit_product.php?id=<?php echo htmlspecialchars($row['ID_produk']); ?>" class="btn btn-icon-only text-edit" title="Kemaskini"><i class="bi bi-pencil-fill"></i></a>                                         
                                        <a href="admin_delete_product.php?id=<?php echo htmlspecialchars($row['ID_produk']); ?>" 
                                        class="btn btn-icon-only text-delete" 
                                        title="Padam" 
                                        onclick="return confirm('Adakah anda pasti mahu memadam produk ini? Tindakan ini tidak boleh dibatalkan.');">
                                        <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">Tiada produk ditemui yang sepadan.</td></tr>
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