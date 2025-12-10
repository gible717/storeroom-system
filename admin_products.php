<?php
// admin_products.php - Product inventory listing with filters & pagination

$pageTitle = "Pengurusan Produk";
require 'admin_header.php';

if ($conn === null || $conn->connect_error) {
    die("<div class='container-fluid'><div class='alert alert-danger'>Ralat Sambungan Pangkalan Data.</div></div>");
}

// Get categories & suppliers for filter dropdowns
$kategori_result = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI ORDER BY nama_kategori ASC");
$supplier_result = $conn->query("SELECT DISTINCT nama_pembekal FROM barang WHERE nama_pembekal IS NOT NULL AND nama_pembekal != '' ORDER BY nama_pembekal ASC");

// Build WHERE clause based on filters
$where_clauses = [];
$params = [];
$types = '';

// Status filter
$status_filter = $_GET['status'] ?? '';
if ($status_filter === 'in_stock') {
    $where_clauses[] = "b.baki_semasa > 10";
} elseif ($status_filter === 'low_stock') {
    $where_clauses[] = "b.baki_semasa > 0 AND b.baki_semasa <= 10";
} elseif ($status_filter === 'out_of_stock') {
    $where_clauses[] = "b.baki_semasa = 0";
}

// Category filter
$category_filter = $_GET['kategori'] ?? '';
if (!empty($category_filter)) {
    $where_clauses[] = "b.ID_kategori = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

// Supplier filter
$supplier_filter = $_GET['pembekal'] ?? '';
if (!empty($supplier_filter)) {
    $where_clauses[] = "b.nama_pembekal = ?";
    $params[] = $supplier_filter;
    $types .= 's';
}

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total rows for pagination
$count_sql = "SELECT COUNT(b.no_kod) AS total FROM barang b LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori";
if (!empty($where_clauses)) {
    $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// Build pagination URL (preserve filters)
$query_params = $_GET;
unset($query_params['page']);
$base_url = http_build_query($query_params);
$base_url = !empty($base_url) ? 'admin_products.php?' . $base_url . '&' : 'admin_products.php?';

// Main query - fetch products with category name
$sql = "SELECT b.no_kod AS ID_produk, b.perihal_stok AS nama_produk, b.harga_seunit AS harga, b.nama_pembekal, b.baki_semasa AS stok_semasa, k.nama_kategori
        FROM barang b LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY b.no_kod ASC LIMIT ? OFFSET ?";

$types .= 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.btn-icon-only { background-color: transparent; border: none; padding: 0.375rem 0.5rem; font-size: 1.1rem; transition: transform 0.2s; }
.btn-icon-only:hover { transform: scale(1.2); }
.text-view { color: #667EEA; }
.text-edit { color: #64748B; }
.text-delete { color: #DC2626; }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Senarai Produk Inventori</h1>
        <div>
            <a href="admin_category.php" class="btn btn-outline-secondary"><i class="bi bi-tags-fill me-1"></i> Urus Kategori</a>
            <a href="admin_add_product.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="admin_products.php" method="GET" id="filterForm">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <!-- Category Filter -->
                <select name="kategori" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Semua Kategori</option>
                    <?php if ($kategori_result && $kategori_result->num_rows > 0):
                        $kategori_result->data_seek(0);
                        while($kategori_row = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($kategori_row['ID_kategori'] ?? ''); ?>" <?php if ($category_filter == $kategori_row['ID_kategori']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($kategori_row['nama_kategori'] ?? ''); ?>
                            </option>
                        <?php endwhile; endif; ?>
                </select>

                <!-- Supplier Filter -->
                <select name="pembekal" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Semua Pembekal</option>
                    <?php if ($supplier_result && $supplier_result->num_rows > 0):
                        while($supplier_row = $supplier_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>" <?php if ($supplier_filter == $supplier_row['nama_pembekal']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>
                            </option>
                        <?php endwhile; endif; ?>
                </select>

                <!-- Status Filter (Malay labels) -->
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Status</option>
                    <option value="in_stock" <?php if ($status_filter === 'in_stock') echo 'selected'; ?>>Stok Mencukupi</option>
                    <option value="low_stock" <?php if ($status_filter === 'low_stock') echo 'selected'; ?>>Stok Rendah</option>
                    <option value="out_of_stock" <?php if ($status_filter === 'out_of_stock') echo 'selected'; ?>>Kehabisan Stok</option>
                </select>
            </div>
        </div>
    </form>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" width="100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Bil.</th>
                            <th>Kod Item</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Nama Pembekal</th>
                            <th>Harga (RM)</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php
                            $bil = $offset + 1; // Start numbering from offset + 1
                            while($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $bil++; ?></td>
                                    <td><?php echo htmlspecialchars($row['ID_produk'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_pembekal'] ?? '-'); ?></td>
                                    <td><?php echo number_format((float)$row['harga'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['stok_semasa'] ?? '0'); ?> unit</td>
                                    <td>
                                        <?php
                                        $stok = (int)$row['stok_semasa'];
                                        if ($stok > 10) echo '<span class="badge bg-success">Stok Mencukupi</span>';
                                        elseif ($stok > 0) echo '<span class="badge bg-warning">Stok Rendah</span>';
                                        else echo '<span class="badge bg-danger">Kehabisan Stok</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon-only text-view" title="Lihat"><i class="bi bi-eye-fill"></i></button>
                                        <a href="admin_edit_product.php?id=<?php echo htmlspecialchars($row['ID_produk'] ?? ''); ?>" class="btn btn-icon-only text-edit" title="Kemaskini"><i class="bi bi-pencil-fill"></i></a>
                                        <a href="admin_delete_product.php?id=<?php echo htmlspecialchars($row['ID_produk'] ?? ''); ?>" class="btn btn-icon-only text-delete" title="Padam" onclick="return confirm('Adakah anda pasti mahu memadam produk ini?');"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">Tiada produk ditemui yang sepadan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card-footer d-flex justify-content-between align-items-center">
            <?php $start_entry = ($total_rows > 0) ? $offset + 1 : 0; $end_entry = $offset + $result->num_rows; ?>
            <small class="text-muted">Showing <?php echo $start_entry; ?> to <?php echo $end_entry; ?> of <?php echo $total_rows; ?> entries</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>">&laquo;</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
