<?php
// FILE: request_list.php
require 'staff_auth_check.php';

// Get the logged-in staff's ID from the session
$id_staf = $_SESSION['ID_staf'];

$sql = "SELECT 
            p.ID_permohonan, 
            p.tarikh_mohon, 
            p.status, 
            COUNT(pb.ID_permohonan_barang) AS bilangan_item,
            -- This new line gets all item names and joins them with a comma
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
$stmt->bind_param('s', $id_staf); // Use the correct variable
$stmt->execute();
$requests_result = $stmt->get_result();
?>
<?php
$pageTitle = "Permohonan Saya";
require 'staff_header.php'; // This one line fixes the entire header and navbar.
?>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); // Clear the message after showing it ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); // Clear the message after showing it ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 fw-bold">Permohonan Saya</h3>
            <a href="kewps8_form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Borang Permohonan Baru
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
                            <option value="Diterima">Diterima</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari Barang...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="requestTable">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">No.</th>
                                <th scope="col">ID Permohonan</th>
                                <th scope="col" class="text-center">Bilangan Item</th>
                                <th scope="col">Tarikh Mohon</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result->num_rows > 0): 
                                $row_number = 1;
                                while ($row = $requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $row_number++; ?></td>
                                        <td class="request-id"><?php echo htmlspecialchars($row['ID_permohonan']); ?></td>
                                        <small class="d-block text-muted item-list"><?php echo htmlspecialchars($row['senarai_barang']); ?></small>
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
                                        <?php if ($row['status'] === 'Belum Diproses'): ?>
                                        <a href="edit_request.php?id=<?php echo $row['ID_permohonan']; ?>" class="btn btn-warning btn-sm" title="Kemaskini">
                                        <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <a href="request_delete.php?id=<?php echo $row['ID_permohonan']; ?>" 
                                            class="btn btn-sm btn-outline-danger" title="Padam"
                                            onclick="return confirm('Adakah anda pasti mahu memadam permohonan ini?');">
                                        <i class="bi bi-trash3-fill"></i>
                                        </a>
    
                                        <?php elseif ($row['status'] === 'Diluluskan'): ?>
                                        <a href="print_request.php?id=<?php echo $row['ID_permohonan']; ?>" target="_blank" class="btn btn-info btn-sm text-white" title="Cetak/Lihat Permohonan">
                                        <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="kewps8_receipt.php?id=<?php echo $row['ID_permohonan']; ?>" target="_blank" class="btn btn-secondary btn-sm" title="Sahkan Penerimaan">
                                        <i class="bi bi-printer-fill"></i>
                                        </a>
        
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                        </td>                
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Tiada permohonan dijumpai.</td></tr>
                            <tr id="no-results-row" style="display: none;"><td colspan="6" class="text-center text-muted">Tiada padanan ditemui.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted small" id="pagination-info">Showing 1-<?php echo $requests_result->num_rows; ?> of <?php echo $requests_result->num_rows; ?></span>
                    <nav><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li></ul></nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.querySelector('#requestTable tbody');
        const tableRows = tableBody.querySelectorAll('tr:not(#no-results-row)'); // Get all data rows
        const noResultsRow = document.getElementById('no-results-row');

        function filterTable() {
            const searchText = searchInput.value.toLowerCase().replace('#', ''); // Allow searching by ID
            const statusText = statusFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                
                // --- UPDATED JS FILTER ---
                // Search the 'request-id' column instead of 'product-name'
            // --- UPDATED JS FILTER (Searches ID and Item List) ---
                const requestId = row.querySelector('.request-id span')?.textContent.toLowerCase().replace('#', '') || '';
                const itemList = row.querySelector('.item-list')?.textContent.toLowerCase() || '';
                const status = row.querySelector('.status-cell')?.textContent.toLowerCase() || '';

                const matchesSearch = searchText === '' || requestId.includes(searchText) || itemList.includes(searchText);
                const matchesStatus = statusText === '' || status === statusText; // Use exact match for status

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            // Show a "no results" message if no rows are visible
            if (noResultsRow) {
                noResultsRow.style.display = visibleRows === 0 ? '' : 'none';
            }
        }

        searchInput.addEventListener('keyup', filterTable);
        statusFilter.addEventListener('change', filterTable);
    });
    </script>
    <?php require 'staff_footer.php'; ?>
</body>
</html>