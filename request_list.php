<?php
// FILE: request_list.php
require 'auth_check.php';

$sql = "SELECT p.ID_permohonan, pr.nama_produk, p.tarikh_mohon, p.jumlah_diminta, p.status 
        FROM permohonan p
        LEFT JOIN produk pr ON p.ID_produk = pr.ID_produk
        WHERE p.ID_staf = ? 
        ORDER BY p.tarikh_mohon DESC, p.ID_permohonan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userID);
$stmt->execute();
$requests_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Saya - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f-9fa; }
        .content-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .alert-top { position: fixed; top: 80px; right: 20px; z-index: 1050; min-width: 300px; }
        .form-control, .form-select { border-radius: 0.5rem; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .input-group-text { background-color: #f8f9fa; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="container my-5">
        <div class="position-relative text-center mb-4">
            <a href="staff_dashboard.php" class="position-absolute start-0 text-dark" title="Kembali ke Dashboard"><i class="bi bi-arrow-left fs-4"></i></a>
            <h3 class="mb-0">Permohonan Saya</h3>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Semua Status</option>
                            <option value="Belum Diproses">Belum Diproses</option>
                            <option value="Diluluskan">Diluluskan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari Produk...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="requestTable">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Nama Produk</th>
                                <th scope="col">Kuantiti</th>
                                <th scope="col">Tarikh</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result->num_rows > 0): 
                                $row_number = 1;
                                while ($row = $requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row_number++; ?></td>
                                        <td class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td><?php echo htmlspecialchars($row['jumlah_diminta']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tarikh_mohon'])); ?></td>
                                        <td class="status-cell">
                                            <?php
                                            $status = htmlspecialchars($row['status']);
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'Diluluskan') $badge_class = 'bg-success';
                                            elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                                            elseif ($status === 'Belum Diproses') $badge_class = 'bg-warning text-dark';
                                            elseif ($status === 'Selesai') $badge_class = 'bg-primary';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'Belum Diproses'): ?>
                                                <a href="edit_request.php?id=<?php echo $row['ID_permohonan']; ?>" class="text-primary me-2" title="Kemaskini"><i class="bi bi-pencil-square"></i></a>
                                            <?php elseif ($row['status'] === 'Diluluskan'): ?>
                                                <a href="print_request.php?id=<?php echo $row['ID_permohonan']; ?>" class="text-secondary" title="Cetak"><i class="bi bi-printer-fill"></i></a>
                                            <?php else: ?>
                                                <a href="view_request.php?id=<?php echo $row['ID_permohonan']; ?>" class="text-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
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
            const tableRows = tableBody.getElementsByTagName('tr');
            const noResultsRow = document.getElementById('no-results-row'); // You might need to add this row if the table could be initially empty.

            function filterTable() {
                const searchText = searchInput.value.toLowerCase();
                const statusText = statusFilter.value.toLowerCase();
                let visibleRows = 0;

                for (let i = 0; i < tableRows.length; i++) {
                    const row = tableRows[i];
                    const productName = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
                    const status = row.querySelector('.status-cell')?.textContent.toLowerCase() || '';
                    
                    const matchesSearch = productName.includes(searchText);
                    const matchesStatus = statusText === '' || status.includes(statusText);

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
                 // Update pagination info (simple version)
                const paginationInfo = document.getElementById('pagination-info');
                if (paginationInfo) {
                    paginationInfo.textContent = `Showing ${visibleRows} of ${tableRows.length} results`;
                }
            }

            searchInput.addEventListener('keyup', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>