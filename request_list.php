<?php
// FILE: request_list.php (VERSI 2.2 - ALL BUGS FIXED)
require 'staff_auth_check.php';

// Get the logged-in staff's ID from the session
$id_staf = $_SESSION['ID_staf'];

// 1. --- GET DATA ---
// We still need 'senarai_barang' for the search function,
// but we will hide it from the table.
$sql = "SELECT 
            p.ID_permohonan, 
            p.tarikh_mohon, 
            p.status, 
            COUNT(pb.ID_permohonan_barang) AS bilangan_item,
            GROUP_CONCAT(b.perihal_stok SEPARATOR ', ') AS senarai_barang
        FROM 
            permohonan p
        LEFT JOIN 
            permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN 
            barang b ON pb.no_kod = b.no_kod
        WHERE 
            p.ID_pemohon = ?
        GROUP BY 
            p.ID_permohonan
        ORDER BY 
            p.tarikh_mohon DESC, p.ID_permohonan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $id_staf);
$stmt->execute();
$requests_result = $stmt->get_result();
$total_rows = $requests_result->num_rows; // Get the total number of rows
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="staff_dashboard.php" class="text-dark me-3" title="Kembali">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
        </div>
        <a href="kewps8_form.php?action=new" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Permohonan Baru
        </a>
    </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <div class="row mb-4">
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
                                        
                                        <td class="request-id fw-bold">#<?php echo htmlspecialchars($row['ID_permohonan']); ?></td>
                                        
                                        <td class="text-center"><?php echo htmlspecialchars($row['bilangan_item']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tarikh_mohon'])); ?></td>
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
                                                <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>" target="_blank" class="btn btn-info btn-sm text-white" title="Lihat Dokumen">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                                <a href="kewps8_print.php?id=<?php echo $row['ID_permohonan']; ?>&print=true" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Dokumen">
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
                    <span class="text-muted small" id="pagination-info">Showing <?php echo $total_rows; ?> of <?php echo $total_rows; ?></span>
                    
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Get all elements ---
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.querySelector('#requestTable tbody');
        const noResultsRow = document.getElementById('no-results-row');
        const originalNoResultsRow = document.getElementById('original-no-results');
        const paginationInfo = document.getElementById('pagination-info');
        
        // --- 2. Get all data rows (this is the key fix) ---
        // We get ALL rows that are not the "template" rows
        const tableRows = tableBody.querySelectorAll('tr:not(#no-results-row):not(#original-no-results)');
        const totalRows = tableRows.length; // Get the count from the rows we just found
        
        // --- 3. The Highlight Function (v2.4 - Inline Style) ---
        // This version forces the style, bypassing all CSS issues.
        function highlightText(cell, text) {
            if (!cell) return;
            
            if (!cell.dataset.originalHtml) {
                cell.dataset.originalHtml = cell.innerHTML;
            }
            let html = cell.dataset.originalHtml;

            if (text) {
                const safeText = text.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp(`(${safeText})`, 'gi');
                
                 // ### THE FIX: We apply the style directly ###
                html = html.replace(regex, '<mark style="background-color:#ffff00; font-weight:bold; padding:0 2px; border-radius:3px;">$1</mark>');
            }
            
            cell.innerHTML = html;
        }

        // --- 4. The Main Filter Function ---
        function filterTable() {
            const searchText = searchInput.value.toLowerCase().replace('#', '');
            const statusText = statusFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                
                const requestIdCell = row.querySelector('.request-id'); // The <td>
                const requestId = requestIdCell?.textContent.toLowerCase().replace('#', '') || '';
                const itemList = row.dataset.itemList || ''; 
                const status = row.querySelector('.status-cell')?.textContent.toLowerCase().trim() || '';

                const matchesSearch = searchText === '' || requestId.includes(searchText) || itemList.includes(searchText);
                const matchesStatus = statusText === '' || status === statusText;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleRows++;
                    // Apply highlight
                    highlightText(requestIdCell, searchText);
                } else {
                    row.style.display = 'none';
                    // Clear highlight
                    highlightText(requestIdCell, ''); 
                }
            }
            
            // --- 5. Show/Hide "No Results" Rows ---
            const hasData = totalRows > 0;
            const hasVisibleRows = visibleRows > 0;

            // Show "Tiada padanan" if we have data, but filter hides it
            if (noResultsRow) {
                noResultsRow.style.display = (hasData && !hasVisibleRows) ? '' : 'none';
            }
            
            // Show "Tiada permohonan" only if the table is completely empty
            if (originalNoResultsRow) {
                originalNoResultsRow.style.display = (!hasData) ? '' : 'none';
            }
            
            // --- 6. Update Pagination Text ---
            if (paginationInfo) {
                paginationInfo.textContent = `Showing ${visibleRows} of ${totalRows}`;
            }
        }

        // --- 7. Attach Event Listeners ---
        searchInput.addEventListener('keyup', filterTable);
        statusFilter.addEventListener('change', filterTable);
        
        // --- 8. Run filter on page load (to fix 0-of-0 bug) ---
        filterTable(); 
    });
    </script>

    <?php require 'staff_footer.php'; ?>
</body>
</html>