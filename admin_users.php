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

$sql .= " ORDER BY nama ASC";

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
                                    <?php if ($user['peranan'] == 'Admin'): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Staf</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="user_view.php?id=<?php echo $user['ID_staf']; ?>" class="btn btn-sm btn-outline-info" title="Lihat">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="user_edit.php?id=<?php echo $user['ID_staf']; ?>" class="btn btn-sm btn-outline-warning" title="Kemaskini">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <?php if ($_SESSION['ID_staf'] !== $user['ID_staf']): ?>
                                        <a href="user_delete.php?id=<?php echo $user['ID_staf']; ?>" 
                                        class="btn btn-sm btn-outline-danger" title="Padam"
                                        onclick="return confirm('Adakah anda pasti mahu memadam pengguna ini?');">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    <?php endif; ?>
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
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted">
                Menunjukkan <?php echo $users->num_rows; ?> rekod
            </span>
            </div>
        
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>