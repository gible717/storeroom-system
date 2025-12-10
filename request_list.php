<?php
// request_list.php - Staff request listing page

require 'staff_auth_check.php';

$id_staf = $_SESSION['ID_staf'];

// Get category filter
$selected_kategori = $_GET['kategori'] ?? '';

// Get all categories for dropdown
$kategori_sql = "SELECT DISTINCT b.kategori
                 FROM barang b
                 INNER JOIN permohonan_barang pb ON b.no_kod = pb.no_kod
                 INNER JOIN permohonan p ON pb.ID_permohonan = p.ID_permohonan
                 WHERE p.ID_pemohon = ? AND b.kategori IS NOT NULL AND b.kategori != ''
                 ORDER BY b.kategori ASC";
$kategori_stmt = $conn->prepare($kategori_sql);
$kategori_stmt->bind_param('s', $id_staf);
$kategori_stmt->execute();
$kategori_result = $kategori_stmt->get_result();
$categories = [];
while ($row = $kategori_result->fetch_assoc()) {
    $categories[] = $row['kategori'];
}
$kategori_stmt->close();

// Build WHERE clause for category filter
$kategori_condition = "";
if ($selected_kategori !== '') {
    $kategori_condition = "AND b.kategori = '" . $conn->real_escape_string($selected_kategori) . "'";
}

// Get all requests for this staff
$sql = "SELECT
            p.ID_permohonan,
            p.tarikh_mohon,
            p.status,
            COUNT(DISTINCT pb.ID_permohonan_barang) AS bilangan_item,
            GROUP_CONCAT(DISTINCT b.perihal_stok SEPARATOR ', ') AS senarai_barang
        FROM
            permohonan p
        LEFT JOIN
            permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN
            barang b ON pb.no_kod = b.no_kod
        WHERE
            p.ID_pemohon = ? $kategori_condition
        GROUP BY
            p.ID_permohonan
        ORDER BY
            p.ID_permohonan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $id_staf);
$stmt->execute();
$requests_result = $stmt->get_result();
$total_rows = $requests_result->num_rows;
?>
<?php
$pageTitle = "Permohonan Saya";
require 'staff_header.php';
?>

<style>
.highlight {
    background-color: rgba(255, 224, 46, 1); 
    font-weight: bold;
    border-radius: 3px;
    padding: 0 2px;
}
</style>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section: Back Arrow | Title | Buat Permohonan Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="staff_dashboard.php" class="text-dark" title="Kembali">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
                <a href="kewps8_form.php?action=new" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Buat Permohonan Baru
                </a>
            </div>

            <div class="card content-card">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <form method="GET" id="categoryFilterForm">
                            <select name="kategori" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $kategori): ?>
                                    <option value="<?php echo htmlspecialchars($kategori); ?>" <?php if ($selected_kategori === $kategori) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($kategori); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Semua Status</option>
                            <option value="Baru">Baru</option>
                            <option value="Diluluskan">Diluluskan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari ID Permohonan atau Barang...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="requestTable">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 5%;">No.</th>
                                <th scope="col" style="width: 20%;">ID Permohonan</th>
                                <th scope="col" class="text-center" style="width: 15%;">Bilangan Item</th>
                                <th scope="col" style="width: 20%;">Tarikh Mohon</th>
                                <th scope="col" class="text-center" style="width: 15%;">Status</th>
                                <th scope="col" class="text-center" style="width: 25%;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_rows > 0): 
                                $row_number = 1;
                                while ($row = $requests_result->fetch_assoc()): ?>
                                    
                                    <tr data-item-list="<?php echo htmlspecialchars(strtolower($row['senarai_barang'])); ?>">
                                        <td class="text-center"><?php echo $row_number++; ?></td>
                                        
                                        <td class="request-id fw-bold">
                                            <button type="button" class="btn btn-link p-0 fw-bold btn-view-details" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailsModal" 
                                                    data-id="<?php echo $row['ID_permohonan']; ?>">
                                                #<?php echo htmlspecialchars($row['ID_permohonan']); ?>
                                            </button>
                                        </td>
                                        
                                        <td class="text-center"><?php echo htmlspecialchars($row['bilangan_item']); ?></td>
                                        <td><?php echo formatMalayDate($row['tarikh_mohon']); ?></td>
                                        <td class="status-cell text-center">
                                            <?php
                                            $status = htmlspecialchars($row['status']);
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'Diluluskan') $badge_class = 'bg-success';
                                            elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                                            elseif ($status === 'Baru') $badge_class = 'bg-warning text-dark';
                                            elseif ($status === 'Selesai') $badge_class = 'bg-primary';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                        </td>
                                        
                                        <td class="text-center">
                                            <?php if ($row['status'] === 'Baru'): ?>
                                                <a href="request_edit.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-warning btn-sm" title="Kemaskini">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                <a href="request_delete.php?id=<?php echo $row['ID_permohonan']; ?>" 
                                                class="btn btn-sm btn-outline-danger btn-delete-request" title="Padam"
                                                data-id="<?php echo $row['ID_permohonan']; ?>">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
                                            
                                            <?php elseif ($row['status'] === 'Diluluskan' || $row['status'] === 'Selesai'): ?>
                                                <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-info btn-sm text-white" title="Lihat Dokumen">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                                <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>&print=true" class="btn btn-secondary btn-sm" title="Cetak Dokumen">
                                                    <i class="bi bi-printer-fill"></i>
                                                </a>
                                            
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="original-no-results"><td colspan="6" class="text-center text-muted">Tiada permohonan dijumpai.</td></tr>
                            <?php endif; ?>
                            
                            <tr id="no-results-row" style="display: none;"><td colspan="6" class="text-center text-muted">Tiada padanan ditemui.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.querySelector('#requestTable tbody');
        const noResultsRow = document.getElementById('no-results-row');
        const originalNoResultsRow = document.getElementById('original-no-results');
        const paginationInfo = document.getElementById('pagination-info');

        // Get all data rows
        const tableRows = tableBody.querySelectorAll('tr:not(#no-results-row):not(#original-no-results)');
        const totalRows = tableRows.length;

        // Highlight search text in cells
        function highlightText(cell, text) {
            if (!cell) return;

            const button = cell.querySelector('button.btn-view-details');

            if (!button) {
                if (cell.dataset.originalHtml) {
                    cell.innerHTML = cell.dataset.originalHtml;
                }
                return;
            }

            // Store original text
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            let html = button.dataset.originalHtml;

            if (text) {
                const safeText = text.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`(${safeText})`, 'gi');
                html = html.replace(regex, '<mark style="background-color:#FFFF00; font-weight:bold; padding:0 2px; border-radius:3px;">$1</mark>');
            }

            button.innerHTML = html;
        }

        // Filter table by search and status
        function filterTable() {
            const searchText = searchInput.value.toLowerCase().replace('#', '');
            const statusText = statusFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                const requestIdCell = row.querySelector('.request-id');
                const requestId = requestIdCell?.textContent.toLowerCase().replace('#', '') || '';
                const itemList = row.dataset.itemList || '';
                const status = row.querySelector('.status-cell')?.textContent.toLowerCase().trim() || '';

                const matchesSearch = searchText === '' || requestId.includes(searchText) || itemList.includes(searchText);
                const matchesStatus = statusText === '' || status === statusText;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleRows++;

                    // Highlight matching text only if there's search text
                    if (searchText && searchText.length > 0) {
                        highlightText(requestIdCell, searchText);
                    } else {
                        // Remove highlights when search is cleared
                        highlightText(requestIdCell, '');
                    }
                } else {
                    row.style.display = 'none';

                    // Remove highlights for hidden rows
                    highlightText(requestIdCell, '');
                }
            }

            // Show/hide no results messages
            const hasData = totalRows > 0;
            const hasVisibleRows = visibleRows > 0;

            // Show "Tiada padanan ditemui" when search has no matches
            if (noResultsRow) {
                noResultsRow.style.display = (hasData && !hasVisibleRows) ? '' : 'none';
            }

            // Show "Tiada permohonan dijumpai" when database is empty
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
                fetch('request_details_ajax.php?id=' + requestId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = `
                                <h6 class="fw-bold">Maklumat Am</h6>
                                <p class_="">
                                    <strong>Jawatan:</strong> ${data.header.jawatan_pemohon || '-'}<br>
                                    <strong>Catatan:</strong> ${data.header.catatan || '-'}
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
                                html += `
                                    <tr>
                                        <td>${item.perihal_stok}</td>
                                        <td class="text-center">${item.kuantiti_mohon}</td>
                                    </tr>
                                `;
                            });

                            html += `
                                    </tbody>
                                </table>
                            `;

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

    <?php require 'staff_footer.php'; ?>
</body>
</html>