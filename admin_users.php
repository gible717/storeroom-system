<?php
// admin_users.php - User management listing

$pageTitle = "Pengurusan Pengguna";
require 'admin_header.php';

// Main query - fetch ALL users (no pagination limit for client-side filtering)
$sql = "SELECT staf.*, jabatan.nama_jabatan
        FROM staf
        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan
        ORDER BY staf.ID_staf ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->get_result();
$total_rows = $users->num_rows;
?>

<style>
/* Role badges - matching new status pill styling */
.role-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-admin {
    background: #cfe2ff;
    color: #084298;
}

.role-staf {
    background: #e2e3e5;
    color: #41464b;
}
</style>

<!-- Page Header -->
<div class="text-center mb-3">
    <h3 class="mb-0 fw-bold">Senarai Pengguna</h3>
</div>
<div class="d-flex justify-content-end mb-4">
    <a href="admin_department.php" class="btn btn-outline-secondary me-2">
        <i class="bi bi-building me-2"></i>Urus Jabatan
    </a>
    <a href="user_add.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Pengguna
    </a>
</div>

<!-- Filter Form -->
<div class="mb-4">
    <div class="row g-3 justify-content-between">
        <div class="col-md-auto">
            <div class="d-flex gap-2">
                <select id="perananFilter" class="form-select">
                    <option value="Semua">Semua Peranan</option>
                    <option value="Admin">Admin</option>
                    <option value="Staf">Staf</option>
                </select>

                <!-- Clear Filters Button -->
                <button id="clearFiltersBtn" class="btn btn-sm btn-outline-secondary" style="display: none;">
                    <i class="bi bi-x-circle me-1"></i>Kosongkan <span id="filterCount" class="badge bg-secondary ms-1"></span>
                </button>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchInput" class="form-control bg-white"
                    placeholder="Cari ID Staf, Nama, Emel...">
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Bil.</th>
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
                        <?php
                        $bil = 1; // Start numbering from 1
                        while ($user = $users->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $bil++; ?></td>
                                <td><?php echo htmlspecialchars($user['ID_staf']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td><?php echo htmlspecialchars($user['emel']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama_jabatan']); ?></td>
                                <td>
                                    <?php
                                    if ($user['is_admin'] == 1) {
                                        echo '<span class="role-badge role-admin">Admin</span>';
                                    } else {
                                        echo '<span class="role-badge role-staf">Staf</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <a href="user_view.php?id=<?php echo htmlspecialchars($user['ID_staf']); ?>" class="btn btn-sm btn-outline-info" title="Lihat">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    <?php
                                    // Admins can manage all users except themselves
                                    $show_buttons = ($user['ID_staf'] != $userID);
                                    ?>

                                    <?php if ($show_buttons): ?>
                                        <a href="user_edit.php?id=<?php echo htmlspecialchars($user['ID_staf']); ?>" class="btn btn-sm btn-outline-warning" title="Kemaskini">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Padam"
                                        onclick="confirmDeleteUser('<?php echo htmlspecialchars($user['ID_staf'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['nama'], ENT_QUOTES); ?>')">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tiada pengguna ditemui.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Info Footer -->
        <div class="card-footer">
            <small class="text-muted">Showing <?php echo $total_rows; ?> of <?php echo $total_rows; ?> entries</small>
        </div>
    </div>
</div>

<script>
// SweetAlert2 delete confirmation
function confirmDeleteUser(userId, userName) {
    Swal.fire({
        title: 'Adakah anda pasti?',
        text: 'Pengguna "' + userName + '" akan dipadam. Tindakan ini tidak boleh dibatalkan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, padamkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'user_delete.php?id=' + encodeURIComponent(userId);
        }
    });
}

// Real-time search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const perananFilter = document.getElementById('perananFilter');
    const tableBody = document.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');

    // Highlight search text
    function highlightText(cell, searchText) {
        if (!cell) return;

        const originalText = cell.textContent;

        // Remove existing highlights
        cell.innerHTML = originalText;

        // Add new highlights if search text exists
        if (searchText && searchText.length > 0) {
            const safeText = searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            const regex = new RegExp(`(${safeText})`, 'gi');
            const highlightedText = originalText.replace(regex, '<mark style="background-color: yellow; padding: 0;">$1</mark>');
            cell.innerHTML = highlightedText;
        }
    }

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().trim();
        const perananText = perananFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            // Skip the "no data" row
            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                row.style.display = 'none';
                return;
            }

            // Get cell data
            const bilCell = row.cells[0]; // Bil. column
            const idStafCell = row.cells[1];
            const namaCell = row.cells[2];
            const emelCell = row.cells[3];
            const jabatanCell = row.cells[4];
            const perananBadge = row.cells[5].querySelector('.badge');

            if (!idStafCell || !namaCell || !emelCell) return;

            const idStaf = idStafCell.textContent.toLowerCase();
            const nama = namaCell.textContent.toLowerCase();
            const emel = emelCell.textContent.toLowerCase();
            const jabatan = jabatanCell.textContent.toLowerCase();
            const peranan = perananBadge ? perananBadge.textContent : '';

            // Check search match
            const matchesSearch = searchText === '' ||
                                idStaf.includes(searchText) ||
                                nama.includes(searchText) ||
                                emel.includes(searchText) ||
                                jabatan.includes(searchText);

            // Check peranan filter
            const matchesPeranan = perananText === 'Semua' || peranan === perananText;

            // Show/hide row
            if (matchesSearch && matchesPeranan) {
                row.style.display = '';
                visibleCount++;

                // Update Bil. column to show correct numbering
                bilCell.textContent = visibleCount;

                // Highlight matching text
                if (searchText && searchText.length > 0) {
                    highlightText(idStafCell, searchText);
                    highlightText(namaCell, searchText);
                    highlightText(emelCell, searchText);
                    highlightText(jabatanCell, searchText);
                } else {
                    // Remove highlights when search is cleared
                    [idStafCell, namaCell, emelCell, jabatanCell].forEach(cell => {
                        cell.innerHTML = cell.textContent;
                    });
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Show "no results" message if needed
        const existingNoResult = tableBody.querySelector('.no-results-row');
        if (existingNoResult) {
            existingNoResult.remove();
        }

        if (visibleCount === 0) {
            const noResultRow = document.createElement('tr');
            noResultRow.className = 'no-results-row';
            noResultRow.innerHTML = '<td colspan="7" class="text-center text-muted py-4">Tiada padanan ditemui.</td>';
            tableBody.appendChild(noResultRow);
        }

        // Update pagination info
        updatePaginationInfo(visibleCount);
    }

    function updatePaginationInfo(visibleCount) {
        const paginationInfo = document.querySelector('.card-footer small');
        if (paginationInfo && visibleCount > 0) {
            paginationInfo.textContent = `Showing ${visibleCount} of ${visibleCount} entries`;
        } else if (paginationInfo) {
            paginationInfo.textContent = 'Showing 0 entries';
        }
    }

    // Check if filters are active and update Clear button
    function updateClearButton() {
        const clearBtn = document.getElementById('clearFiltersBtn');
        const filterCountBadge = document.getElementById('filterCount');
        let activeCount = 0;

        if (searchInput.value.trim()) activeCount++;
        if (perananFilter.value !== 'Semua') activeCount++;

        if (activeCount > 0) {
            clearBtn.style.display = 'inline-block';
            filterCountBadge.textContent = activeCount;
        } else {
            clearBtn.style.display = 'none';
        }
    }

    // Clear all filters
    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        searchInput.value = '';
        perananFilter.value = 'Semua';
        filterTable();
        updateClearButton();
    });

    // Event listeners
    searchInput.addEventListener('keyup', filterTable);
    searchInput.addEventListener('input', function() {
        filterTable();
        updateClearButton();
    });
    perananFilter.addEventListener('change', function() {
        filterTable();
        updateClearButton();
    });
});
</script>

<?php
$stmt->close();
$conn->close();
require 'admin_footer.php';
?>
