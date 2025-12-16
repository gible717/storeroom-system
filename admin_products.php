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

// Main query - fetch ALL products with category name (no pagination limit for client-side filtering)
$sql = "SELECT b.no_kod AS ID_produk, b.perihal_stok AS nama_produk, b.harga_seunit AS harga, b.nama_pembekal, b.baki_semasa AS stok_semasa, k.nama_kategori
        FROM barang b LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
        ORDER BY b.no_kod ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$total_rows = $result->num_rows;
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
    <div class="text-center mb-3">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Senarai Produk</h1>
    </div>

    <!-- Filter & Search -->
    <div class="mb-4">
        <div class="row g-3 justify-content-between">
            <!-- Left side - Filters -->
            <div class="col-auto">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Category Filter -->
                    <select id="kategoriFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Kategori</option>
                        <?php if ($kategori_result && $kategori_result->num_rows > 0):
                            $kategori_result->data_seek(0);
                            while($kategori_row = $kategori_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($kategori_row['nama_kategori'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($kategori_row['nama_kategori'] ?? ''); ?>
                                </option>
                            <?php endwhile; endif; ?>
                    </select>

                    <!-- Supplier Filter -->
                    <select id="pembekalFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Pembekal</option>
                        <?php if ($supplier_result && $supplier_result->num_rows > 0):
                            while($supplier_row = $supplier_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>
                                </option>
                            <?php endwhile; endif; ?>
                    </select>

                    <!-- Status Filter (Malay labels) -->
                    <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Semua Status</option>
                        <option value="Stok Mencukupi">Stok Mencukupi</option>
                        <option value="Stok Rendah">Stok Rendah</option>
                        <option value="Kehabisan Stok">Kehabisan Stok</option>
                    </select>

                    <!-- Clear Filters Button -->
                    <button id="clearFiltersBtn" class="btn btn-sm btn-outline-secondary" style="display: none;">
                        <i class="bi bi-x-circle me-1"></i>Kosongkan <span id="filterCount" class="badge bg-secondary ms-1"></span>
                    </button>
                </div>
            </div>

            <!-- Right side - Search & Actions -->
            <div class="col-auto d-flex align-items-center gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control bg-white"
                        placeholder="Cari Kod, Nama Produk...">
                </div>
                <a href="admin_category.php" class="btn btn-outline-secondary"><i class="bi bi-tags-fill me-1"></i> Urus Kategori</a>
                <a href="admin_add_product.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
            </div>
        </div>
    </div>

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
                            $bil = 1; // Start numbering from 1
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

        <!-- Info Footer -->
        <div class="card-footer">
            <small class="text-muted">Showing <?php echo $total_rows; ?> of <?php echo $total_rows; ?> entries</small>
        </div>
    </div>
</div>

<script>
// Real-time search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('kategoriFilter');
    const pembekalFilter = document.getElementById('pembekalFilter');
    const statusFilter = document.getElementById('statusFilter');
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
        const kategoriText = kategoriFilter.value;
        const pembekalText = pembekalFilter.value;
        const statusText = statusFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            // Skip the "no data" row
            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                row.style.display = 'none';
                return;
            }

            // Get cell data (based on table structure)
            const bilCell = row.cells[0]; // Bil. column
            const kodItemCell = row.cells[1];
            const namaProdukCell = row.cells[2];
            const kategoriCell = row.cells[3];
            const pembekalCell = row.cells[4];
            const statusBadge = row.cells[7].querySelector('.badge');

            if (!kodItemCell || !namaProdukCell || !kategoriCell) return;

            const kodItem = kodItemCell.textContent.toLowerCase();
            const namaProduk = namaProdukCell.textContent.toLowerCase();
            const kategori = kategoriCell.textContent.toLowerCase();
            const pembekal = pembekalCell.textContent.toLowerCase();
            const status = statusBadge ? statusBadge.textContent : '';

            // Check search match (Kod Item, Nama Produk, Kategori, Pembekal)
            const matchesSearch = searchText === '' ||
                                kodItem.includes(searchText) ||
                                namaProduk.includes(searchText) ||
                                kategori.includes(searchText) ||
                                pembekal.includes(searchText);

            // Check kategori filter
            const matchesKategori = kategoriText === '' || kategori === kategoriText.toLowerCase();

            // Check pembekal filter
            const matchesPembekal = pembekalText === '' || pembekal === pembekalText.toLowerCase();

            // Check status filter
            const matchesStatus = statusText === '' || status === statusText;

            // Show/hide row
            if (matchesSearch && matchesKategori && matchesPembekal && matchesStatus) {
                row.style.display = '';
                visibleCount++;

                // Update Bil. column to show correct numbering
                bilCell.textContent = visibleCount;

                // Highlight matching text
                if (searchText && searchText.length > 0) {
                    highlightText(kodItemCell, searchText);
                    highlightText(namaProdukCell, searchText);
                    highlightText(kategoriCell, searchText);
                    highlightText(pembekalCell, searchText);
                } else {
                    // Remove highlights when search is cleared
                    [kodItemCell, namaProdukCell, kategoriCell, pembekalCell].forEach(cell => {
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
            noResultRow.innerHTML = '<td colspan="9" class="text-center text-muted py-4">Tiada padanan ditemui.</td>';
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
        if (kategoriFilter.value) activeCount++;
        if (pembekalFilter.value) activeCount++;
        if (statusFilter.value) activeCount++;

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
        kategoriFilter.value = '';
        pembekalFilter.value = '';
        statusFilter.value = '';
        filterTable();
        updateClearButton();
    });

    // Event listeners
    searchInput.addEventListener('keyup', filterTable);
    searchInput.addEventListener('input', function() {
        filterTable();
        updateClearButton();
    });
    kategoriFilter.addEventListener('change', function() {
        filterTable();
        updateClearButton();
    });
    pembekalFilter.addEventListener('change', function() {
        filterTable();
        updateClearButton();
    });
    statusFilter.addEventListener('change', function() {
        filterTable();
        updateClearButton();
    });
});
</script>

<?php require 'admin_footer.php'; ?>
