<?php
// FILE: manage_requests.php (Corrected and Final Version)
$pageTitle = "Pengurusan Permohonan";
require 'admin_header.php'; // This one line fixes the entire layout.

// This SQL query is excellent. It correctly joins the tables and sorts by status.
$sql = "SELECT 
            pr.ID_permohonan, 
            s.nama, 
            p.nama_produk, 
            pr.tarikh_mohon, 
            pr.jumlah_diminta, 
            pr.status 
        FROM permohonan pr
        JOIN staf s ON pr.ID_staf = s.ID_staf
        JOIN produk p ON pr.ID_produk = p.ID_produk
        ORDER BY 
            CASE pr.status
                WHEN 'Belum Diproses' THEN 1
                WHEN 'Diluluskan' THEN 2
                WHEN 'Ditolak' THEN 3
                ELSE 4
            END, pr.tarikh_mohon DESC";

$requests_result = $conn->query($sql);
?>

<style>
    .btn-sm i { font-size: 1rem; vertical-align: middle; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Pengurusan Permohonan</h3>
    </div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="row mb-3">
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
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-0" id="searchInput" placeholder="Cari Permohonan...">
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
                        <th class="text-center">Tindakan</th>
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
                                <td class="text-center">
    <?php if ($row['status'] === 'Belum Diproses'): ?>
        <a href="update_request_status.php?id=<?php echo $row['ID_permohonan']; ?>&action=approve" class="btn btn-success btn-sm" title="Luluskan"><i class="bi bi-check-lg"></i></a>
        <a href="update_request_status.php?id=<?php echo $row['ID_permohonan']; ?>&action=reject" class="btn btn-danger btn-sm" title="Tolak"><i class="bi bi-x-lg"></i></a>
    
    <?php elseif ($row['status'] === 'Diluluskan'): ?>
        <a href="#" class="btn btn-info btn-sm text-white" title="Lihat Dokumen">
            <i class="bi bi-eye-fill"></i>
        </a>
        <a href="print_request.php?id=<?php echo $row['ID_permohonan']; ?>" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Dokumen">
            <i class="bi bi-printer-fill"></i>
        </a>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Tiada permohonan ditemui.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Your existing filter JavaScript can go here if needed, or be moved to a central admin_footer.php script.
</script>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>