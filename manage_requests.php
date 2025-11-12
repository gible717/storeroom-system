<?php
// FILE: manage_requests.php (FINAL, CLEANED VERSION)
$pageTitle = "Pengurusan Permohonan";
require 'admin_header.php'; // Use the admin header

// This is the correct, "System 2" (multi-item) compatible SQL query
$sql = "SELECT p.ID_permohonan, p.tarikh_mohon, p.status, s.nama,
               COUNT(pb.ID_permohonan_barang) AS bilangan_item,
               GROUP_CONCAT(b.perihal_stok SEPARATOR ', ') AS senarai_barang
        FROM permohonan p
        JOIN staf s ON p.ID_pemohon = s.ID_staf
        LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN barang b ON pb.no_kod = b.no_kod
        GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, s.nama
        ORDER BY 
           CASE p.status
               WHEN 'Baru' THEN 1
               WHEN 'Diluluskan' THEN 2
               WHEN 'Selesai' THEN 3
               WHEN 'Ditolak' THEN 4
               ELSE 5
           END, p.tarikh_mohon DESC";
$requests_result = $conn->query($sql);
$total_rows = $requests_result ? $requests_result->num_rows : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Pengurusan Permohonan</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="row mb-3">
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
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-0" id="searchInput" placeholder="Cari ID, Staf, atau Item...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="requestTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">ID Permohonan</th>
                        <th style="width: 15%;">Nama Staf</th>
                        <th style="width: 30%;">Senarai Item</th>
                        <th style="width: 10%;" class="text-center">Bil. Item</th>
                        <th style="width: 10%;">Tarikh</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                        <th style="width: 15%;" class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_rows > 0): ?>
                        <?php while ($row = $requests_result->fetch_assoc()): ?>
                            <tr class="data-row" 
                                data-staf="<?php echo htmlspecialchars(strtolower($row['nama'])); ?>"
                                data-item-list="<?php echo htmlspecialchars(strtolower($row['senarai_barang'] ?? '')); ?>">
                                
                                <td class="fw-bold request-id">#<?php echo htmlspecialchars($row['ID_permohonan']); ?></td>
                                <td class="staf-name"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td class="item-list"><small><?php echo htmlspecialchars($row['senarai_barang'] ?? 'Tiada Item'); ?></small></td>
                                <td class="text-center"><?php echo htmlspecialchars($row['bilangan_item']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tarikh_mohon'])); ?></td>
                                <td class="status-cell text-center">
                                    <?php
                                    $status = trim(htmlspecialchars($row['status'])); // Trim status
                                    $badge_class = 'bg-secondary';
                                    if ($status === 'Diluluskan') $badge_class = 'bg-success';
                                    elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                                    elseif ($status === 'Baru') $badge_class = 'bg-warning text-dark';
                                    elseif ($status === 'Selesai') $badge_class = 'bg-primary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>

                                <td class="text-center">
                                    <?php 
                                    $status = trim($row['status']); // We must trim() again here

                                    if ($status === 'Baru'): 
                                    ?>
                                        <a href="request_review.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-primary btn-sm" title="Semak Permohonan">
                                            Semak
                                        </a>
                                    
                                    <?php elseif ($status === 'Diluluskan' || $status === 'Selesai'): ?>
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
                        <tr id="original-no-results"><td colspan="7" class="text-center text-muted py-4">Tiada permohonan ditemui.</td></tr>
                    <?php endif; ?>
                    
                    <tr id="no-results-row" style="display: none;"><td colspan="7" class="text-center text-muted py-4">Tiada padanan ditemui.</td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small" id="pagination-info">Showing <?php echo $total_rows; ?> of <?php
echo $total_rows; ?></span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.querySelector('#requestTable tbody');
    const tableRows = tableBody.querySelectorAll('tr.data-row'); // Only select data rows
    const noResultsRow = document.getElementById('no-results-row');
    const originalNoResultsRow = document.getElementById('original-no-results');
    const paginationInfo = document.getElementById('pagination-info');
    const totalRows = <?php echo $total_rows; ?>;

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().replace('#', '');
        const statusText = statusFilter.value.toLowerCase();
        let visibleRows = 0;

        for (let i = 0; i < tableRows.length; i++) {
            const row = tableRows[i];
            
            // Get text content from cells and data attributes
            const requestId = row.querySelector('.request-id')?.textContent.toLowerCase().replace('#', '') || '';
            const stafName = row.dataset.staf || '';
            const itemList = row.dataset.itemList || '';
            const status = row.querySelector('.status-cell')?.textContent.toLowerCase().trim() || '';

            // Check for matches
            const matchesSearch = searchText === '' || 
                                  requestId.includes(searchText) || 
                                  stafName.includes(searchText) || 
                                  itemList.includes(searchText);
            
            const matchesStatus = statusText === '' || status === statusText;

            if (matchesSearch && matchesStatus) {
                row.style.display = ''; // Show row
                visibleRows++;
            } else {
                row.style.display = 'none'; // Hide row
            }
        }
        
        // Show/Hide the "No Results" messages
        if (totalRows > 0) {
            // If table has data, show "no match" message on filter fail
            noResultsRow.style.display = visibleRows === 0 ? '' : 'none';
            if (originalNoResultsRow) originalNoResultsRow.style.display = 'none';
        } else {
            // If table is empty, "no match" is irrelevant
            noResultsRow.style.display = 'none';
            if (originalNoResultsRow) originalNoResultsRow.style.display = '';
        }

        // Update pagination text
        paginationInfo.textContent = `Showing ${visibleRows} of ${totalRows}`;
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>