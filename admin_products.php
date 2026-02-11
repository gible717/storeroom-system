<?php
// admin_products.php - Product inventory listing with filters

$pageTitle = "Pengurusan Produk";
require 'admin_header.php';

if ($conn === null || $conn->connect_error) {
    die("<div class='container-fluid'><div class='alert alert-danger'>Ralat Sambungan Pangkalan Data.</div></div>");
}

// Get categories for filter dropdown (hierarchical)
$kategori_filter_result = $conn->query("
    SELECT k.ID_kategori, k.nama_kategori, k.parent_id, p.nama_kategori AS parent_name
    FROM KATEGORI k
    LEFT JOIN KATEGORI p ON k.parent_id = p.ID_kategori
    ORDER BY COALESCE(k.parent_id, k.ID_kategori), k.parent_id IS NOT NULL, k.nama_kategori ASC
");
// Organize into tree for filter dropdown
$filter_main_cats = [];
$filter_sub_cats = [];
if ($kategori_filter_result) {
    while ($krow = $kategori_filter_result->fetch_assoc()) {
        if ($krow['parent_id'] === null) {
            $filter_main_cats[$krow['ID_kategori']] = $krow;
        } else {
            $filter_sub_cats[$krow['parent_id']][] = $krow;
        }
    }
}

$supplier_result = $conn->query("SELECT DISTINCT nama_pembekal FROM barang WHERE nama_pembekal IS NOT NULL AND nama_pembekal != '' ORDER BY nama_pembekal ASC");

// Main query - fetch ALL products with category name, parent category name, and product image
$sql = "SELECT b.no_kod AS ID_produk, b.perihal_stok AS nama_produk, b.harga_seunit AS harga, b.nama_pembekal, b.baki_semasa AS stok_semasa, b.gambar_produk,
               k.nama_kategori, pk.nama_kategori AS parent_kategori_name
        FROM barang b
        LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
        LEFT JOIN KATEGORI pk ON k.parent_id = pk.ID_kategori
        ORDER BY b.no_kod ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$total_rows = $result->num_rows;

// Summary stats
$stats_sql = "SELECT
    SUM(CASE WHEN baki_semasa = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN baki_semasa > 0 AND baki_semasa <= 10 THEN 1 ELSE 0 END) as low_stock,
    COALESCE(SUM(harga_seunit * baki_semasa), 0) as total_value
FROM barang";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['out_of_stock' => 0, 'low_stock' => 0, 'total_value' => 0];
?>

<style>
/* --- Page Header --- */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.page-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: #212529;
}
.page-header .header-actions {
    display: flex;
    gap: 0.5rem;
}

/* --- Filter Card --- */
.filter-card {
    border: none;
    border-radius: 1rem;
    margin-bottom: 1.5rem;
}
.filter-card .card-body {
    padding: 1rem 1.25rem;
}

/* --- Search bar --- */
.search-products {
    max-width: 280px;
}
.search-products .input-group-text {
    border-right: none;
}
.search-products .form-control {
    border-left: none;
}
.search-products .form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

/* --- Table Enhancements --- */
.products-table-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
}

.products-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #6c757d;
    padding: 0.75rem 1rem;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.products-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    font-size: 0.875rem;
}

.products-table tbody tr {
    transition: background-color 0.15s ease;
}
.products-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* --- Product Thumbnail --- */
.product-thumb {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    object-fit: cover;
}
.product-thumb-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    background: linear-gradient(135deg, #f1f3f5 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 1rem;
}

/* --- Stock Badges --- */
.stock-badge {
    padding: 0.3rem 0.7rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.stock-mencukupi {
    background: #d1e7dd;
    color: #0a3622;
}
.stock-rendah {
    background: #fff3cd;
    color: #997404;
}
.stock-habis {
    background: #f8d7da;
    color: #58151c;
}

/* --- Action Buttons --- */
.btn-action-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    border: none;
    background: transparent;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    padding: 0;
}
.btn-action-icon.view {
    color: #4f46e5;
}
.btn-action-icon.view:hover {
    background: #eef2ff;
    color: #4338ca;
}
.btn-action-icon.edit {
    color: #64748b;
}
.btn-action-icon.edit:hover {
    background: #f1f5f9;
    color: #475569;
}
.btn-action-icon.delete {
    color: #dc3545;
}
.btn-action-icon.delete:hover {
    background: #fef2f2;
    color: #b91c1c;
}

/* --- Filter dropdowns --- */
.filter-select {
    font-size: 0.8rem;
    border-radius: 50px;
    padding: 0.35rem 2rem 0.35rem 0.85rem;
    border: 1.5px solid #dee2e6;
    color: #495057;
    background-color: #fff;
    transition: border-color 0.2s ease;
    width: auto !important;
}
.filter-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.15rem rgba(79, 70, 229, 0.15);
}

/* --- Clear filters button --- */
.btn-clear-filters {
    font-size: 0.8rem;
    border-radius: 50px;
    padding: 0.35rem 0.85rem;
    border: 1.5px solid #dee2e6;
    background: #fff;
    color: #6c757d;
    transition: all 0.2s ease;
}
.btn-clear-filters:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

/* --- Tambah Produk button --- */
.btn-tambah-produk {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.btn-tambah-produk:hover {
    background-color: #4338ca;
    border-color: #4338ca;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

/* --- Urus Kategori button --- */
.btn-urus-kategori {
    border: 1.5px solid #dee2e6;
    color: #495057;
    border-radius: 0.5rem;
    font-weight: 500;
    background: #fff;
    transition: all 0.2s ease;
}
.btn-urus-kategori:hover {
    border-color: #4f46e5;
    color: #4f46e5;
    background: #eef2ff;
}

/* --- Product name styling --- */
.product-name-cell {
    font-weight: 500;
    color: #212529;
}
.product-kod-cell {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 0.8rem;
    color: #6c757d;
}

/* --- Stock Progress Bar --- */
.stock-progress {
    height: 4px;
    border-radius: 2px;
    background: #e9ecef;
    margin-top: 4px;
    width: 60px;
}
.stock-progress-bar {
    height: 100%;
    border-radius: 2px;
}

/* --- Summary Stat Cards --- */
.summary-stat-card {
    border: none;
    border-radius: 0.75rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.summary-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
.summary-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}
.summary-stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
    margin: 0;
}

/* --- Card footer --- */
.table-footer {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 0.75rem 1.25rem;
    border-radius: 0 0 1rem 1rem;
    font-size: 0.8rem;
    color: #6c757d;
}
</style>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Senarai Produk</h1>
            <small class="text-muted"><?php echo $total_rows; ?> produk dalam inventori</small>
        </div>
        <div class="header-actions">
            <a href="admin_category.php" class="btn btn-urus-kategori">
                <i class="bi bi-tags-fill me-1"></i> Urus Kategori
            </a>
            <a href="admin_add_product.php" class="btn btn-tambah-produk">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card summary-stat-card shadow-sm">
                <div class="card-body py-3 px-3 text-center">
                    <div class="summary-stat-value" style="color:#4f46e5;">
                        <i class style="font-size:1rem;opacity:0.5;"></i>
                        <?php echo $total_rows; ?>
                    </div>
                    <p class="summary-stat-label">Jumlah Produk</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-stat-card shadow-sm">
                <div class="card-body py-3 px-3 text-center">
                    <div class="summary-stat-value" style="color:#198754;">
                        RM <?php echo number_format((float)$stats['total_value'], 0); ?>
                    </div>
                    <p class="summary-stat-label">Nilai Inventori</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-stat-card shadow-sm">
                <div class="card-body py-3 px-3 text-center">
                    <div class="summary-stat-value" style="color:#ffc107;">
                        <i class= style="font-size:1rem;opacity:0.5;"></i>
                        <?php echo (int)$stats['low_stock']; ?>
                    </div>
                    <p class="summary-stat-label">Stok Rendah</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-stat-card shadow-sm">
                <div class="card-body py-3 px-3 text-center">
                    <div class="summary-stat-value" style="color:#dc3545;">
                        <i class= style="font-size:1rem;opacity:0.5;"></i>
                        <?php echo (int)$stats['out_of_stock']; ?>
                    </div>
                    <p class="summary-stat-label">Kehabisan Stok</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card filter-card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Filters -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select id="kategoriFilter" class="form-select form-select-sm filter-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($filter_main_cats as $main): ?>
                            <option value="<?php echo htmlspecialchars($main['nama_kategori']); ?>">
                                <?php echo htmlspecialchars($main['nama_kategori']); ?>
                            </option>
                            <?php if (isset($filter_sub_cats[$main['ID_kategori']])): ?>
                                <?php foreach ($filter_sub_cats[$main['ID_kategori']] as $sub): ?>
                                    <option value="<?php echo htmlspecialchars($sub['nama_kategori']); ?>">
                                        &nbsp;&nbsp;&nbsp;&#x2514; <?php echo htmlspecialchars($sub['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <select id="pembekalFilter" class="form-select form-select-sm filter-select">
                        <option value="">Semua Pembekal</option>
                        <?php if ($supplier_result && $supplier_result->num_rows > 0):
                            while($supplier_row = $supplier_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($supplier_row['nama_pembekal'] ?? ''); ?>
                                </option>
                            <?php endwhile; endif; ?>
                    </select>

                    <select id="statusFilter" class="form-select form-select-sm filter-select">
                        <option value="">Semua Status</option>
                        <option value="Stok Mencukupi">Stok Mencukupi</option>
                        <option value="Stok Rendah">Stok Rendah</option>
                        <option value="Kehabisan Stok">Kehabisan Stok</option>
                    </select>

                    <button id="clearFiltersBtn" class="btn btn-sm btn-clear-filters" style="display: none;">
                        <i class="bi bi-x-circle me-1"></i>Kosongkan <span id="filterCount" class="badge bg-secondary ms-1"></span>
                    </button>
                </div>

                <!-- Search -->
                <div class="input-group search-products">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-start-0 bg-white"
                        placeholder="Cari kod, nama produk...">
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card products-table-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-accessible" tabindex="0" role="region" aria-label="Senarai produk inventori">
                <table class="table table-hover products-table mb-0" width="100%">
                    <caption class="visually-hidden">Senarai semua produk dalam inventori dengan maklumat kategori, pembekal, harga dan status stok</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 50px;">Bil.</th>
                            <th scope="col" style="width: 56px;">Foto</th>
                            <th scope="col" data-sort="kod" data-type="text">Kod Item</th>
                            <th scope="col" data-sort="nama" data-type="text">Nama Produk</th>
                            <th scope="col" data-sort="kategori" data-type="text">Kategori</th>
                            <th scope="col" data-sort="pembekal" data-type="text">Pembekal</th>
                            <th scope="col" data-sort="harga" data-type="number">Harga (RM)</th>
                            <th scope="col" data-sort="stok" data-type="number">Stok</th>
                            <th scope="col">Status</th>
                            <th scope="col" style="width: 110px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php
                            $bil = 1;
                            while($row = $result->fetch_assoc()):
                            $productName = htmlspecialchars($row['nama_produk'] ?? '');
                            $productId = htmlspecialchars($row['ID_produk'] ?? '');
                            $gambar = $row['gambar_produk'] ?? null;
                            $has_image = (!empty($gambar) && file_exists($gambar));
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $bil++; ?></td>
                                    <td>
                                        <?php if ($has_image): ?>
                                            <img src="<?php echo htmlspecialchars($gambar); ?>"
                                                 class="product-thumb"
                                                 alt="<?php echo $productName; ?>">
                                        <?php else: ?>
                                            <div class="product-thumb-placeholder">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="product-kod-cell"><?php echo $productId; ?></td>
                                    <td class="product-name-cell"><?php echo $productName; ?></td>
                                    <td>
                                        <?php
                                        if (!empty($row['parent_kategori_name'])) {
                                            echo htmlspecialchars($row['parent_kategori_name']) . ' <i class="bi bi-chevron-right" style="font-size:0.65rem;color:#adb5bd;"></i> ' . htmlspecialchars($row['nama_kategori']);
                                        } else {
                                            echo htmlspecialchars($row['nama_kategori'] ?? '-');
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nama_pembekal'] ?? '-'); ?></td>
                                    <td><?php echo number_format((float)$row['harga'], 2); ?></td>
                                    <td>
                                        <?php
                                        $stok_val = (int)($row['stok_semasa'] ?? 0);
                                        $bar_pct = min(($stok_val / 50) * 100, 100);
                                        $bar_color = $stok_val > 10 ? '#198754' : ($stok_val > 0 ? '#ffc107' : '#dc3545');
                                        ?>
                                        <div><?php echo $stok_val; ?> unit</div>
                                        <div class="stock-progress"><div class="stock-progress-bar" style="width:<?php echo $bar_pct; ?>%;background:<?php echo $bar_color; ?>;"></div></div>
                                    </td>
                                    <td>
                                        <?php
                                        $stok = (int)$row['stok_semasa'];
                                        if ($stok > 10) echo '<span class="stock-badge stock-mencukupi">Stok Mencukupi</span>';
                                        elseif ($stok > 0) echo '<span class="stock-badge stock-rendah">Stok Rendah</span>';
                                        else echo '<span class="stock-badge stock-habis">Kehabisan Stok</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php
                                            $kategori_display = $row['nama_kategori'] ?? '-';
                                            if (!empty($row['parent_kategori_name'])) {
                                                $kategori_display = $row['parent_kategori_name'] . ' > ' . $row['nama_kategori'];
                                            }
                                            ?>
                                            <button class="btn-action-icon view" title="Lihat" aria-label="Lihat butiran <?php echo $productName; ?>"
                                                    data-id="<?php echo $productId; ?>"
                                                    data-name="<?php echo $productName; ?>"
                                                    data-kategori="<?php echo htmlspecialchars($kategori_display); ?>"
                                                    data-pembekal="<?php echo htmlspecialchars($row['nama_pembekal'] ?? '-'); ?>"
                                                    data-harga="<?php echo number_format((float)$row['harga'], 2); ?>"
                                                    data-stok="<?php echo (int)$row['stok_semasa']; ?>"
                                                    data-gambar="<?php echo $has_image ? htmlspecialchars($gambar) : ''; ?>">
                                                <i class="bi bi-eye-fill" aria-hidden="true"></i>
                                            </button>
                                            <a href="admin_edit_product.php?id=<?php echo $productId; ?>" class="btn-action-icon edit" title="Kemaskini" aria-label="Kemaskini <?php echo $productName; ?>">
                                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                            </a>
                                            <button type="button" class="btn-action-icon delete" title="Padam" aria-label="Padam <?php echo $productName; ?>"
                                                    onclick="confirmDelete('<?php echo htmlspecialchars($row['ID_produk'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['nama_produk'] ?? '', ENT_QUOTES); ?>')">
                                                <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state empty-state-table">
                                        <i class="bi bi-box-seam empty-state-icon"></i>
                                        <h5 class="empty-state-title">Tiada Produk</h5>
                                        <p class="empty-state-text">Inventori anda masih kosong. Mulakan dengan menambah produk pertama anda.</p>
                                        <a href="admin_add_product.php" class="btn btn-tambah-produk btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table Footer -->
        <div class="table-footer d-flex justify-content-between align-items-center">
            <span id="tableInfo">Menunjukkan <?php echo $total_rows; ?> daripada <?php echo $total_rows; ?> produk</span>
        </div>
    </div>
</div>

<script>
// SweetAlert2 delete confirmation
function confirmDelete(productId, productName) {
    Swal.fire({
        title: 'Adakah anda pasti?',
        html: 'Produk <strong>"' + productName + '"</strong> akan dipadam.<br><small class="text-muted">Tindakan ini tidak boleh dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, padamkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            window.location.href = 'admin_delete_product.php?id=' + encodeURIComponent(productId) + '&token=' + encodeURIComponent(csrfToken);
        }
    });
}

// Real-time search and filter
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('kategoriFilter');
    const pembekalFilter = document.getElementById('pembekalFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.querySelector('.products-table tbody');
    const rows = tableBody.querySelectorAll('tr');

    // Highlight search text
    function highlightText(cell, searchText) {
        if (!cell) return;
        const originalText = cell.textContent;
        cell.innerHTML = originalText;
        if (searchText && searchText.length > 0) {
            const safeText = searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            const regex = new RegExp(`(${safeText})`, 'gi');
            cell.innerHTML = originalText.replace(regex, '<mark style="background-color: yellow; padding: 0;">$1</mark>');
        }
    }

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().trim();
        const kategoriText = kategoriFilter.value;
        const pembekalText = pembekalFilter.value;
        const statusText = statusFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                row.style.display = 'none';
                return;
            }

            // Cell indices (Foto column at index 1)
            const bilCell = row.cells[0];
            const kodItemCell = row.cells[2];
            const namaProdukCell = row.cells[3];
            const kategoriCell = row.cells[4];
            const pembekalCell = row.cells[5];
            const statusBadge = row.cells[8] ? row.cells[8].querySelector('.stock-badge') : null;

            if (!kodItemCell || !namaProdukCell || !kategoriCell) return;

            const kodItem = kodItemCell.textContent.toLowerCase();
            const namaProduk = namaProdukCell.textContent.toLowerCase();
            const kategori = kategoriCell.textContent.toLowerCase();
            const pembekal = pembekalCell ? pembekalCell.textContent.toLowerCase() : '';
            const status = statusBadge ? statusBadge.textContent.trim() : '';

            const matchesSearch = searchText === '' ||
                                kodItem.includes(searchText) ||
                                namaProduk.includes(searchText) ||
                                kategori.includes(searchText) ||
                                pembekal.includes(searchText);

            const matchesKategori = kategoriText === '' || kategori.includes(kategoriText.toLowerCase());
            const matchesPembekal = pembekalText === '' || pembekal === pembekalText.toLowerCase();
            const matchesStatus = statusText === '' || status === statusText;

            if (matchesSearch && matchesKategori && matchesPembekal && matchesStatus) {
                row.style.display = '';
                visibleCount++;
                bilCell.textContent = visibleCount;

                if (searchText && searchText.length > 0) {
                    highlightText(kodItemCell, searchText);
                    highlightText(namaProdukCell, searchText);
                    highlightText(kategoriCell, searchText);
                    highlightText(pembekalCell, searchText);
                } else {
                    [kodItemCell, namaProdukCell, kategoriCell, pembekalCell].forEach(cell => {
                        if (cell) cell.innerHTML = cell.textContent;
                    });
                }
            } else {
                row.style.display = 'none';
            }
        });

        // No results row
        const existingNoResult = tableBody.querySelector('.no-results-row');
        if (existingNoResult) existingNoResult.remove();

        if (visibleCount === 0) {
            const noResultRow = document.createElement('tr');
            noResultRow.className = 'no-results-row';
            noResultRow.innerHTML = `<td colspan="10">
                <div class="empty-state empty-state-table">
                    <i class="bi bi-search empty-state-icon"></i>
                    <h5 class="empty-state-title">Tiada Padanan</h5>
                    <p class="empty-state-text">Tiada produk yang sepadan dengan carian atau penapis anda.</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('clearFiltersBtn').click()">
                        <i class="bi bi-x-circle me-1"></i> Kosongkan Penapis
                    </button>
                </div>
            </td>`;
            tableBody.appendChild(noResultRow);
        }

        // Update footer info
        const tableInfo = document.getElementById('tableInfo');
        const totalRows = <?php echo $total_rows; ?>;
        if (tableInfo) {
            tableInfo.textContent = visibleCount === totalRows
                ? `Menunjukkan ${totalRows} produk`
                : `Menunjukkan ${visibleCount} daripada ${totalRows} produk`;
        }
    }

    // Clear filters button
    function updateClearButton() {
        const clearBtn = document.getElementById('clearFiltersBtn');
        const filterCountBadge = document.getElementById('filterCount');
        let activeCount = 0;

        if (searchInput.value.trim()) activeCount++;
        if (kategoriFilter.value) activeCount++;
        if (pembekalFilter.value) activeCount++;
        if (statusFilter.value) activeCount++;

        if (activeCount > 0) {
            clearBtn.style.display = 'inline-flex';
            filterCountBadge.textContent = activeCount;
        } else {
            clearBtn.style.display = 'none';
        }
    }

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        searchInput.value = '';
        kategoriFilter.value = '';
        pembekalFilter.value = '';
        statusFilter.value = '';
        filterTable();
        updateClearButton();
    });

    // View product detail popup
    document.querySelectorAll('.btn-action-icon.view').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            const stok = parseInt(d.stok);
            let statusHtml = '';
            if (stok > 10) statusHtml = '<span class="stock-badge stock-mencukupi">Stok Mencukupi</span>';
            else if (stok > 0) statusHtml = '<span class="stock-badge stock-rendah">Stok Rendah</span>';
            else statusHtml = '<span class="stock-badge stock-habis">Kehabisan Stok</span>';

            const photoHtml = d.gambar
                ? `<img src="${d.gambar}" style="max-width:100%;max-height:200px;object-fit:contain;border-radius:0.75rem;margin-bottom:1rem;">`
                : `<div style="width:100%;height:120px;background:linear-gradient(135deg,#f1f3f5,#e9ecef);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:3rem;margin-bottom:1rem;"><i class="bi bi-image"></i></div>`;

            Swal.fire({
                html: `
                    ${photoHtml}
                    <h5 style="font-weight:700;margin-bottom:0.25rem;">${d.name}</h5>
                    <small class="text-muted" style="font-family:monospace;">${d.id}</small>
                    <hr style="margin:0.75rem 0;">
                    <div style="text-align:left;font-size:0.85rem;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kategori</span>
                            <strong>${d.kategori}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pembekal</span>
                            <strong>${d.pembekal}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Harga Seunit</span>
                            <strong>RM ${d.harga}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Stok</span>
                            <div><strong class="me-2">${d.stok} unit</strong>${statusHtml}</div>
                        </div>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: '<i class="bi bi-pencil-fill me-1"></i>Kemaskini',
                width: 420
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = 'admin_edit_product.php?id=' + encodeURIComponent(d.id);
                }
            });
        });
    });

    // Event listeners
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

    // Initialize sortable table - add table ID first
    const productsTable = document.querySelector('.products-table');
    if (productsTable) {
        productsTable.id = 'productsTable';
        initSortableTable('productsTable');
    }
});
</script>

<?php require 'admin_footer.php'; ?>
