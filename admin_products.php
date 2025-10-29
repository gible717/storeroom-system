<?php
// FILE: admin_products.php (NOW 100% "SLAYED")
$pageTitle = "Pengurusan Produk";
require 'admin_header.php'; // This now includes db.php

// "GHOST" (BUG) 1: "KILLED" (DELETED) the extra 'require db.php'.

if ($conn === null || $conn->connect_error) {
    die("<div class='container-fluid'><div class='alert alert-danger'>Ralat Sambungan Pangkalan Data.</div></div>");
}

// --- "SLAY" (STRATEGIST) FIX 2: NEW KATEGORI QUERY ---
// This now "vibes" (gets) from your NEW KATEGORI table.
$kategori_result = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI ORDER BY nama_kategori ASC");

// --- Filter Logic Starts Here ---
$where_clauses = [];
$params = [];
$types = '';

// Status Filter (NOW "SLAYED" with 'p.' alias)
$status_filter = $_GET['status'] ?? '';
if ($status_filter === 'in_stock') {
    $where_clauses[] = "p.stok_semasa > 10";
} elseif ($status_filter === 'low_stock') {
    $where_clauses[] = "p.stok_semasa > 0 AND p.stok_semasa <= 10";
} elseif ($status_filter === 'out_of_stock') {
    $where_clauses[] = "p.stok_semasa = 0";
}

// --- "SLAY" (STRATEGIST) FIX 3: NEW KATEGORI FILTER LOGIC ---
// This "ghost" (bug) is "slain". We now filter by the ID.
$category_filter = $_GET['kategori'] ?? ''; // This will be the ID
if (!empty($category_filter)) {
    $where_clauses[] = "p.ID_kategori = ?"; // 'p.' is the "vibe" (alias) for PRODUK
    $params[] = $category_filter;
    $types .= 'i'; // 'i' for Integer (it's an ID, not text)
}

// --- START: PAGINATION LOGIC ---

// 1. Define Variables
$limit = 7; // 7 entries per page as requested
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Get Total Row Count (with filters)
$count_sql = "SELECT COUNT(p.ID_produk) AS total
            FROM PRODUK p
            LEFT JOIN KATEGORI k ON p.ID_kategori = k.ID_kategori";
if (!empty($where_clauses)) {
    $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$count_stmt = $conn->prepare($count_sql);
if ($count_stmt === false) { die("Error preparing count query: " . $conn->error); }
if (!empty($params)) {
    // Note: We only bind the filter params here (s or i), not the pagination params yet
    $count_stmt->bind_param($types, ...$params); 
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// 5. Build Base URL for Links (preserves filters)
$query_params = $_GET; 
unset($query_params['page']); 
$base_url = http_build_query($query_params); 
if (!empty($base_url)) {
    $base_url = 'admin_products.php?' . $base_url . '&';
} else {
    $base_url = 'admin_products.php?';
}
// --- END: PAGINATION LOGIC ---

// --- "SLAY" (STRATEGIST) FIX 4: NEW "STEAK" (JOIN) QUERY ---
// This "slays" the "Fatal Error".
$sql = "SELECT p.ID_produk, p.nama_produk, p.harga, p.stok_semasa,
            k.nama_kategori 
        FROM PRODUK p
        LEFT JOIN KATEGORI k ON p.ID_kategori = k.ID_kategori"; // This is the "smart" (UX) JOIN

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY p.ID_produk ASC LIMIT ? OFFSET ?"; // Added LIMIT and OFFSET

// Add the pagination types ('ii' for limit, offset) and values to our params
$types .= 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Aaaaa! Query failed! The 'Kernel' says: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<style>
.btn-icon-only {
    background-color: transparent;
    border: none;
    padding: 0.375rem 0.5rem;
    font-size: 1.1rem;
    transition: transform 0.2s ease-in-out;
}
.btn-icon-only:hover {
    transform: scale(1.2);
}
.text-view {
    color: #667EEA;
}
.text-edit {
    color: #64748B;
}
.text-delete {
    color: #DC2626;
}
</style>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Senarai Produk Inventori</h1>
        
        <div>
            <a href="admin_category.php" class="btn btn-outline-secondary">
                <i class="bi bi-tags-fill me-1"></i> Urus Kategori
            </a>
            <a href="admin_add_product.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </a>
        </div>
        </div>

    <form action="admin_products.php" method="GET" id="filterForm">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                
                <select name="kategori" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Semua Kategori</option>
                    <?php 
                    if ($kategori_result && $kategori_result->num_rows > 0):
                        // We must "rewind" this result to use it
                        $kategori_result->data_seek(0); 
                        while($kategori_row = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($kategori_row['ID_kategori']); ?>" <?php if ($category_filter == $kategori_row['ID_kategori']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($kategori_row['nama_kategori']); ?>
                            </option>
                        <?php endwhile; 
                    endif;
                    ?>
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
                                    
                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                    
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
        <?php
        // Calculate starting and ending entry numbers
        $start_entry = ($total_rows > 0) ? $offset + 1 : 0;
        $end_entry = $offset + $result->num_rows;
        ?>
        <small class="text-muted">Showing <?php echo $start_entry; ?> to <?php echo $end_entry; ?> of <?php echo $total_rows; ?> entries</small>

        <nav aria-label="Product pagination">
            <ul class="pagination pagination-sm mb-0">
                
                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    </div>
</div>

<?php
// "GHOST" (BUG) 7: "KILLED" (DELETED) the extra 'conn->close()'.
require 'admin_footer.php';
?>