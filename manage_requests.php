<?php
// manage_requests.php - Admin request management page

$pageTitle = "Pengurusan Permohonan";
require 'admin_header.php';
?>

<style>
/* Glowing animation for "Baru" status badge - text only */
@keyframes pulse-glow {
    0% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
    50% {
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.8), 0 0 30px rgba(255, 193, 7, 0.6), 0 0 40px rgba(255, 193, 7, 0.4);
    }
    100% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
}

/* Status badges */
.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-baru {
    background: #fff3cd;
    color: #997404;
    animation: pulse-glow 2s ease-in-out infinite;
}
.status-diluluskan { background: #d1e7dd; color: #0a3622; }
.status-ditolak { background: #f8d7da; color: #58151c; }
</style>

<?php
// Get all categories for dropdown filter
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Get all requests with item details and category info
$sql = "SELECT p.ID_permohonan, p.tarikh_mohon, p.status, p.catatan_admin, p.ID_pemohon, s.nama,
            COUNT(pb.ID_permohonan_barang) AS bilangan_item,
            GROUP_CONCAT(DISTINCT b.perihal_stok SEPARATOR ', ') AS senarai_barang,
            GROUP_CONCAT(DISTINCT b.kategori SEPARATOR ', ') AS kategori_list
        FROM permohonan p
        JOIN staf s ON p.ID_pemohon = s.ID_staf
        LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN barang b ON pb.no_kod = b.no_kod
        GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, p.catatan_admin, p.ID_pemohon, s.nama
        ORDER BY
        CASE p.status
            WHEN 'Baru' THEN 1
            WHEN 'Diluluskan' THEN 2
            WHEN 'Selesai' THEN 3
            WHEN 'Ditolak' THEN 4
            ELSE 5
        END, p.ID_permohonan DESC";
$requests_result = $conn->query($sql);
$total_rows = $requests_result ? $requests_result->num_rows : 0;
?>

<!-- Page Header -->
<div class="text-center mb-3">
    <h3 class="mb-0 fw-bold">Senarai Permohonan</h3>
</div>
<div class="d-flex justify-content-end mb-4">
    <a href="kewps8_browse.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Buat Permohonan
    </a>
</div>

<!-- Requests Table Card -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="Baru">Baru</option>
                    <option value="Diluluskan">Diluluskan</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="kategoriFilter">
                    <option value="">Semua Kategori</option>
                    <?php
                    if ($kategori_result && $kategori_result->num_rows > 0) {
                        while ($kategori = $kategori_result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($kategori['kategori']) . '">' . htmlspecialchars($kategori['kategori']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-0" id="searchInput" placeholder="Cari ID, Staf, atau Item...">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="requestTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">ID Permohonan</th>
                        <th style="width: 15%;">Nama Staf</th>
                        <th style="width: 30%;">Senarai Item</th>
                        <th style="width: 10%;" class="text-center">Bil. Item</th>
                        <th style="width: 10%;">Tarikh Mohon</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                        <th style="width: 15%;" class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_rows > 0): ?>
                        <?php while ($row = $requests_result->fetch_assoc()): ?>
                            <tr class="data-row"
                                data-staf="<?php echo htmlspecialchars(strtolower($row['nama'])); ?>"
                                data-item-list="<?php echo htmlspecialchars(strtolower($row['senarai_barang'] ?? '')); ?>"
                                data-kategori="<?php echo htmlspecialchars(strtolower($row['kategori_list'] ?? '')); ?>">

                                <td class="fw-bold request-id">
                                    <button type="button" class="btn btn-link p-0 fw-bold btn-view-details"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailsModal"
                                            data-id="<?php echo $row['ID_permohonan']; ?>">
                                        #<?php echo htmlspecialchars($row['ID_permohonan']); ?>
                                    </button>
                                </td>
                                <td class="staf-name"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td class="item-list"><small><?php echo htmlspecialchars($row['senarai_barang'] ?? 'Tiada Item'); ?></small></td>
                                <td class="text-center"><?php echo htmlspecialchars($row['bilangan_item']); ?></td>
                                <td><?php echo formatMalayDate($row['tarikh_mohon']); ?></td>
                                <td class="status-cell text-center">
                                    <?php
                                    $status = trim(htmlspecialchars($row['status']));
                                    $badge_class = 'status-badge';
                                    if ($status === 'Diluluskan') $badge_class .= ' status-diluluskan';
                                    elseif ($status === 'Ditolak') $badge_class .= ' status-ditolak';
                                    elseif ($status === 'Baru') $badge_class .= ' status-baru';
                                    ?>
                                    <span class="<?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>

                                <td class="text-center">
                                    <?php
                                    $status = trim($row['status']);
                                    $catatan_admin = trim($row['catatan_admin'] ?? '');

                                    if ($status === 'Baru'):
                                        // Check if current admin is the request owner
                                        $is_owner = isset($row['ID_pemohon']) && $row['ID_pemohon'] === $userID;
                                    ?>
                                        <?php if ($is_owner): ?>
                                        <!-- Owner can only edit, not approve their own request -->
                                        <a href="admin_request_edit.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-outline-warning btn-sm" title="Edit Item">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php else: ?>
                                        <!-- Other admins can approve/reject -->
                                        <a href="request_review.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-primary btn-sm" title="Semak Permohonan">
                                            Semak
                                        </a>
                                        <?php endif; ?>

                                    <?php elseif ($status === 'Diluluskan' || $status === 'Selesai'): ?>
                                        <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-info btn-sm text-white" title="Lihat Dokumen">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>&print=true" class="btn btn-secondary btn-sm" title="Cetak Dokumen">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>

                                    <?php elseif (!empty($catatan_admin)): ?>
                                        <div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-chat-left-text-fill me-1"></i>
                                                <strong>Catatan:</strong><br>
                                                <em><?php echo htmlspecialchars($catatan_admin); ?></em>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="original-no-results"><td colspan="7" class="text-center text-muted py-4">Tiada permohonan ditemui.</td></tr>
                    <?php endif; ?>

                    <tr id="no-results-row" style="display: none;"><td colspan="7" class="text-center text-muted py-4">Tiada padanan ditemui.</td></tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small" id="pagination-info">
                <?php if ($total_rows > 0): ?>
                    Showing 1 to <?php echo $total_rows; ?> of <?php echo $total_rows; ?> entries
                <?php else: ?>
                    Showing 0 to 0 of 0 entries
                <?php endif; ?>
            </span>

            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
// Filter table by search and status
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const kategoriFilter = document.getElementById('kategoriFilter');
    const tableBody = document.querySelector('#requestTable tbody');
    const tableRows = tableBody.querySelectorAll('tr.data-row');
    const noResultsRow = document.getElementById('no-results-row');
    const originalNoResultsRow = document.getElementById('original-no-results');
    const paginationInfo = document.getElementById('pagination-info');
    const totalRows = <?php echo $total_rows; ?>;

    // Highlight search text
    function highlightText(cell, searchText) {
        if (!cell) return;

        // Check if cell contains a button (for ID Permohonan column)
        const button = cell.querySelector('button.btn-view-details');

        if (button) {
            // Store original text in button
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            let html = button.dataset.originalHtml;

            if (searchText && searchText.length > 0) {
                const safeText = searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`(${safeText})`, 'gi');
                html = html.replace(regex, '<mark style="background-color:#FFFF00; font-weight:bold; padding:0 2px; border-radius:3px;">$1</mark>');
            }

            button.innerHTML = html;
        } else {
            // For regular cells
            if (!cell.dataset.originalHtml) {
                cell.dataset.originalHtml = cell.innerHTML;
            }
            let html = cell.dataset.originalHtml;

            if (searchText && searchText.length > 0) {
                const safeText = searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`(${safeText})`, 'gi');
                html = html.replace(regex, '<mark style="background-color:#FFFF00; font-weight:bold; padding:0 2px; border-radius:3px;">$1</mark>');
            }

            cell.innerHTML = html;
        }
    }

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().replace('#', '');
        const statusText = statusFilter.value.toLowerCase();
        const kategoriText = kategoriFilter.value.toLowerCase();
        let visibleRows = 0;

        for (let i = 0; i < tableRows.length; i++) {
            const row = tableRows[i];

            const requestIdCell = row.querySelector('.request-id');
            const stafNameCell = row.querySelector('.staf-name');
            const itemListCell = row.querySelector('.item-list');

            const requestId = requestIdCell?.textContent.toLowerCase().replace('#', '') || '';
            const stafName = row.dataset.staf || '';
            const itemList = row.dataset.itemList || '';
            const kategoriList = row.dataset.kategori || '';
            const status = row.querySelector('.status-cell')?.textContent.toLowerCase().trim() || '';

            const matchesSearch = searchText === '' ||
                                requestId.includes(searchText) ||
                                stafName.includes(searchText) ||
                                itemList.includes(searchText);

            const matchesStatus = statusText === '' || status === statusText;
            const matchesKategori = kategoriText === '' || kategoriList.includes(kategoriText);

            if (matchesSearch && matchesStatus && matchesKategori) {
                row.style.display = '';
                visibleRows++;

                // Highlight matching text only if there's search text
                if (searchText && searchText.length > 0) {
                    highlightText(requestIdCell, searchText);
                    highlightText(stafNameCell, searchText);
                    highlightText(itemListCell, searchText);
                } else {
                    // Remove highlights when search is cleared
                    highlightText(requestIdCell, '');
                    highlightText(stafNameCell, '');
                    highlightText(itemListCell, '');
                }
            } else {
                row.style.display = 'none';

                // Remove highlights for hidden rows
                highlightText(requestIdCell, '');
                highlightText(stafNameCell, '');
                highlightText(itemListCell, '');
            }
        }

        // Show/hide no results messages
        const hasData = totalRows > 0;
        const hasVisibleRows = visibleRows > 0;

        // Show "Tiada padanan ditemui" when search has no matches
        if (noResultsRow) {
            noResultsRow.style.display = (hasData && !hasVisibleRows) ? '' : 'none';
        }

        // Show "Tiada permohonan ditemui" when database is empty
        if (originalNoResultsRow) {
            originalNoResultsRow.style.display = (!hasData) ? '' : 'none';
        }

        // Update pagination info
        if (paginationInfo) {
            if (visibleRows > 0) {
                paginationInfo.textContent = `Showing 1 to ${visibleRows} of ${totalRows} entries`;
            } else {
                paginationInfo.textContent = `Showing 0 to 0 of ${totalRows} entries`;
            }
        }
    }

    // Attach event listeners
    searchInput.addEventListener('keyup', filterTable);
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    kategoriFilter.addEventListener('change', filterTable);

    // Run filter on page load to set initial state
    filterTable();

    // Quick view modal logic
    const detailsModal = document.getElementById('detailsModal');
    const detailsModalTitle = document.getElementById('detailsModalLabel');
    const detailsModalBody = document.getElementById('detailsModalBody');
    const viewButtons = document.querySelectorAll('.btn-view-details');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.dataset.id;

            detailsModalTitle.textContent = 'Maklumat Permohonan #' + requestId;
            detailsModalBody.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

            // Fetch request details
            fetch('admin_request_details_ajax.php?id=' + requestId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <h6 class="fw-bold">Maklumat Am</h6>
                            <p>
                                <strong>Nama Pemohon:</strong> ${data.header.nama_pemohon || '-'}<br>
                                <strong>Jawatan:</strong> ${data.header.jawatan_pemohon || '-'}
                            </p>
                            <hr>
                            <h6 class="fw-bold">Senarai Item (${data.items.length})</h6>
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Perihal Stok</th>
                                        <th class="text-center">Kuantiti Mohon</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        data.items.forEach(item => {
                            let kategoriHtml = '';
                            if (item.kategori) {
                                kategoriHtml = `<br><span class="badge bg-light text-dark border" style="font-size:0.65rem;">${item.kategori}`;
                                if (item.subkategori) {
                                    kategoriHtml += ` <i class="bi bi-chevron-right" style="font-size:0.5rem;"></i> ${item.subkategori}`;
                                }
                                kategoriHtml += `</span>`;
                            }
                            html += `
                                <tr>
                                    <td>${item.perihal_stok}${kategoriHtml}</td>
                                    <td class="text-center">${item.kuantiti_mohon}</td>
                                </tr>
                            `;
                        });

                        html += `
                                </tbody>
                            </table>
                        `;

                        // Show staff's remarks if exists
                        if (data.header.catatan && data.header.catatan.trim() !== '') {
                            html += `
                                <hr>
                                <h6 class="fw-bold">Catatan Pemohon</h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-chat-left-text me-2"></i>
                                    ${data.header.catatan.replace(/\n/g, '<br>')}
                                </div>
                            `;
                        }

                        // Show admin remarks if exists (for rejected/approved requests)
                        if (data.header.catatan_admin && data.header.catatan_admin.trim() !== '') {
                            html += `
                                <hr>
                                <h6 class="fw-bold">Catatan Pelulus</h6>
                                <div class="alert alert-warning">
                                    <i class="bi bi-person-badge me-2"></i>
                                    ${data.header.catatan_admin.replace(/\n/g, '<br>')}
                                </div>
                            `;
                        }

                        detailsModalBody.innerHTML = html;

                    } else {
                        detailsModalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    detailsModalBody.innerHTML = '<div class="alert alert-danger">Gagal menghubungi server.</div>';
                });
        });
    });
});
</script>

<!-- Quick View Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Maklumat Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
            </div>
        </div>
    </div>
</div>

<?php
require 'admin_footer.php';
?>
