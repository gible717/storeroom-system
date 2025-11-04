<?php
// FILE: admin_users.php
$pageTitle = "Pengurusan Pengguna";
require 'admin_header.php';

// --- Filtering and Search Logic ---
$filter_peranan = $_GET['peranan'] ?? 'Semua';
$search_query = $_GET['search'] ?? '';

// Base SQL
$sql = "SELECT staf.*, jabatan.nama_jabatan 
        FROM staf 
        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan";
$params = [];
$types = "";
$where_clauses = [];

// Add filter for 'Peranan'
if ($filter_peranan !== 'Semua') {
    $where_clauses[] = "peranan = ?";
    $params[] = $filter_peranan;
    $types .= "s";
}

// Add filter for 'Search'
if (!empty($search_query)) {
    // Search by name OR email
    $where_clauses[] = "(nama LIKE ? OR emel LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Combine all WHERE clauses
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// --- START: PAGINATION LOGIC ---

// 1. Define Variables
$limit = 7; // 7 entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Get Total Row Count (with filters)
$count_sql = "SELECT COUNT(staf.ID_staf) AS total 
            FROM staf 
            LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan";
if (!empty($where_clauses)) {
    $count_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$count_stmt = $conn->prepare($count_sql);
if ($count_stmt === false) { die("Error preparing count query: " . $conn->error); }
if (!empty($params)) {
    // Bind the same filter params
    $count_stmt->bind_param($types, ...$params); 
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// 5. Build Base URL for Links (preserves filters)
$query_params = $_GET; // Get all current query params
unset($query_params['page']); // Remove 'page' to avoid duplication
$base_url = http_build_query($query_params); // Rebuild (e.g., "peranan=Admin&search=test")
if (!empty($base_url)) {
    $base_url = 'admin_users.php?' . $base_url . '&';
} else {
    $base_url = 'admin_users.php?';
}
// --- END: PAGINATION LOGIC ---

$sql .= " ORDER BY nama ASC LIMIT ? OFFSET ?";

// Add the pagination types ('ii' for limit, offset) and values
$types .= 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Pengurusan Pengguna</h3>
    <div>
        <a href="admin_department.php" class="btn btn-outline-secondary me-2">
            <i class="bi bi-building me-2"></i>Urus Jabatan
        </a>
        <a href="user_add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Pengguna
        </a>
    </div>
</div>
        <form method="GET" id="filterForm" class="mb-4">
            <div class="row g-3 justify-content-between">
                
                <div class="col-md-auto">
                    <select name="peranan" class="form-select" onchange="this.form.submit()">
                        <option value="Semua" <?php if($filter_peranan == 'Semua') echo 'selected'; ?>>Semua Peranan</option>
                        <option value="Admin" <?php if($filter_peranan == 'Admin') echo 'selected'; ?>>Admin</option>
                        <option value="Staf" <?php if($filter_peranan == 'Staf') echo 'selected'; ?>>Staf</option>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 0.375rem 0 0 0.375rem;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-white border-start-0" 
                            placeholder="Cari Pengguna..." value="<?php echo htmlspecialchars($search_query); ?>"
                            style="border-radius: 0 0.375rem 0 0.375rem;">
                    </div>
                </div>
                
            </div>
        </form>
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Staf</th>
                        <th>Nama</th>
                        <th>Emel</th>
                        <th>Jabatan</th>
                        <th>Peranan</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['ID_staf']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td><?php echo htmlspecialchars($user['emel']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama_jabatan']); ?></td>
                                <td>
                                <?php 
                                // --- THIS IS THE "STEAK" (FIXED) CODE ---
                                if ($user['is_superadmin'] == 1) {
                                echo '<span class="badge bg-danger">Super Admin</span>';
                                } elseif ($user['is_admin'] == 1) {
                                echo '<span class="badge bg-primary">Admin</span>';
                                } else {
                                echo '<span class="badge bg-secondary">Staf</span>';
                                }
                                ?>
                                </td>
                                <td class="text-end">
    <a href="user_view.php?id=<?php echo htmlspecialchars($user['ID_staf']); ?>" class="btn btn-sm btn-outline-info" title="Lihat">
        <i class="bi bi-eye-fill"></i>
    </a>

    <?php
    // --- THIS IS THE "STEAK" (FIX) ---
    // $is_superadmin (logged-in user) comes from admin_auth_check.php
    // $user['is_admin'] is the user in the row
    
    // We show the buttons IF:
    // 1. You are a Super Admin
    //    OR
    // 2. You are a regular Admin AND the user in the row is Staff (is_admin == 0)
    
    $show_buttons = false;
    if ($is_superadmin) {
        $show_buttons = true;
    } elseif ($user['is_admin'] == 0) { // You are a regular Admin, and this user is Staff
        $show_buttons = true;
    }
    ?>

    <?php if ($show_buttons): ?>
        
        <a href="user_edit.php?id=<?php echo htmlspecialchars($user['ID_staf']); ?>" class="btn btn-sm btn-outline-warning" title="Kemaskini">
            <i class="bi bi-pencil-fill"></i>
        </a>

        <?php // The "4x4" (safe) check: don't let anyone delete themselves ?>
        <?php if ($_SESSION['ID_staf'] !== $user['ID_staf']): ?>
            
            <a href="user_delete.php?id=<?php echo htmlspecialchars($user['ID_staf']); ?>" 
            class="btn btn-sm btn-outline-danger" title="Padam"
            onclick="return confirm('Adakah anda pasti mahu memadam pengguna ini?');">
                <i class="bi bi-trash3-fill"></i>
            </a>
        <?php endif; ?>

        <?php endif; // --- END OF "STEAK" (FIX) --- ?>
                        </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tiada pengguna ditemui.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
        <?php
        // Calculate starting and ending entry numbers
        $start_entry = ($total_rows > 0) ? $offset + 1 : 0;
        $end_entry = $offset + $users->num_rows;
        ?>
        <small class="text-muted">Showing <?php echo $start_entry; ?> to <?php echo $end_entry; ?> of <?php echo $total_rows; ?> entries</small>

        <nav aria-label="User pagination">
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
<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>