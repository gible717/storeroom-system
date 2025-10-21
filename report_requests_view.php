<?php
// FILE: report_requests_view.php
$pageTitle = "Laporan Permohonan";
require 'admin_header.php';

// --- Filter Logic ---
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($status_filter !== 'Semua') {
    $where_clause .= " AND p.status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

// --- Main SQL Query ---
$sql = "SELECT 
            p.*,
            s.nama AS nama_staf,
            pr.nama_produk
        FROM 
            permohonan p
        JOIN 
            staf s ON p.ID_staf = s.ID_staf
        JOIN 
            produk pr ON p.ID_produk = pr.ID_produk
        $where_clause
        ORDER BY 
            p.tarikh_mohon DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$requests = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Laporan Permohonan</h3>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
    </button>
</div>

<p>
    <strong>Julat Tarikh:</strong> <?php echo date('d M Y', strtotime($tarikh_mula)); ?>
    <strong>Hingga:</strong> <?php echo date('d M Y', strtotime($tarikh_akhir)); ?><br>
    <strong>Status:</strong> <?php echo htmlspecialchars($status_filter); ?>
</p>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Permohonan</th>
                        <th>Tarikh Mohon</th>
                        <th>Nama Staf</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Kuantiti</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Tarikh Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests && $requests->num_rows > 0): ?>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td>REQ-<?php echo str_pad($row['ID_permohonan'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tarikh_mohon'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_staf']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="text-center"><?php echo $row['jumlah_diminta']; ?></td>
                                <td><?php echo htmlspecialchars($row['catatan']); ?></td>
                                <td>
                                    <?php
                                        $status = $row['status'];
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'Diluluskan') $badge_class = 'bg-success';
                                        if ($status == 'Belum Diproses') $badge_class = 'bg-warning text-dark';
                                        if ($status == 'Ditolak') $badge_class = 'bg-danger';
                                        if ($status == 'Selesai') $badge_class = 'bg-info';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td>
                                    <?php echo $row['tarikh_selesai'] ? date('d M Y', strtotime($row['tarikh_selesai'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Tiada permohonan ditemui untuk tetapan ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>