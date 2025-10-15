<?php
// FILE: manage_requests.php
require 'auth_check.php';

if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php");
    exit;
}

$sql = "SELECT p.ID_permohonan, s.nama, pr.nama_produk, p.tarikh_mohon, p.jumlah_diminta, p.status 
        FROM permohonan p
        JOIN staf s ON p.ID_staf = s.ID_staf
        JOIN produk pr ON p.ID_produk = pr.ID_produk
        ORDER BY 
            CASE p.status
                WHEN 'Belum Diproses' THEN 1
                WHEN 'Diluluskan' THEN 2
                WHEN 'Ditolak' THEN 3
                ELSE 4
            END, p.tarikh_mohon DESC";

$requests_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Permohonan - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .content-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.8rem; }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Pengurusan Permohonan</h3>
            <a href="request_form.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Permohonan
            </a>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Semua Status</option>
                            <option value="Belum Diproses">Belum Diproses</option>
                            <option value="Diluluskan">Diluluskan</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari Permohonan...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="requestTable">
                        <thead>
                            <tr>
                                <th>ID Permohonan</th>
                                <th>Nama Staf</th>
                                <th>Nama Produk</th>
                                <th>Kuantiti</th>
                                <th>Tarikh</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                                <?php while ($row = $requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>REQ<?php echo str_pad($row['ID_permohonan'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td class="search-field"><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td class="search-field"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td><?php echo htmlspecialchars($row['jumlah_diminta']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tarikh_mohon'])); ?></td>
                                        <td class="status-cell">
                                            <?php
                                            $status = htmlspecialchars($row['status']);
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'Diluluskan') $badge_class = 'bg-success';
                                            elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                                            elseif ($status === 'Belum Diproses') $badge_class = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'Belum Diproses'): ?>
                                                <a href="update_request_status.php?id=<?php echo $row['ID_permohonan']; ?>&action=approve" class="btn btn-success btn-sm" title="Luluskan"><i class="bi bi-check-lg"></i></a>
                                                <a href="update_request_status.php?id=<?php echo $row['ID_permohonan']; ?>&action=reject" class="btn btn-danger btn-sm" title="Tolak"><i class="bi bi-x-lg"></i></a>
                                            <?php else: ?>
                                                <a href="#" class="btn btn-outline-secondary btn-sm" title="Lihat"><i class="bi bi-eye"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="no-results-row" style="display: none;"><td colspan="7" class="text-center text-muted">Tiada padanan ditemui.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for Search and Filter
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const tableBody = document.querySelector('#requestTable tbody');
            const tableRows = tableBody.getElementsByTagName('tr');

            function filterTable() {
                const searchText = searchInput.value.toLowerCase();
                const statusText = statusFilter.value.toLowerCase();

                for (let row of tableRows) {
                    const searchFields = row.querySelectorAll('.search-field');
                    const statusCell = row.querySelector('.status-cell');
                    
                    let textMatch = false;
                    if (searchFields.length > 0) {
                        searchFields.forEach(field => {
                            if (field.textContent.toLowerCase().includes(searchText)) {
                                textMatch = true;
                            }
                        });
                    }

                    const statusMatch = statusText === '' || (statusCell && statusCell.textContent.toLowerCase().includes(statusText));

                    if (textMatch && statusMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            searchInput.addEventListener('keyup', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>