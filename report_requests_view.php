<?php
$pageTitle = "Laporan Permohonan";
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
// Malay month formatting function
function format_malay_date($date_string) {
    $malay_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $date = strtotime($date_string);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}

// Filter logic
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'Semua';
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($status_filter !== 'Semua') {
    $where_clause .= " AND p.status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

if ($kategori_filter !== 'Semua') {
    $where_clause .= " AND b.kategori = ? ";
    $params[] = $kategori_filter;
    $types .= "s";
}

// --- Main SQL Query ---
$sql = "SELECT
            p.ID_permohonan,
            p.tarikh_mohon,
            p.status,
            p.tarikh_lulus,
            p.catatan,
            s.nama AS nama_staf,
            GROUP_CONCAT(DISTINCT b.perihal_stok SEPARATOR ', ') AS nama_produk,
            SUM(pb.kuantiti_mohon) AS jumlah_diminta
        FROM
            permohonan p
        JOIN
            staf s ON p.ID_pemohon = s.ID_staf
        LEFT JOIN
            permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN
            barang b ON pb.no_kod = b.no_kod
        $where_clause
        GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, p.tarikh_lulus, p.catatan, s.nama
        ORDER BY
            p.tarikh_mohon DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$requests = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="report_requests.php" class="btn btn-link nav-link p-0 me-3" title="Kembali ke Pilihan Laporan">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Permohonan</h3>
    </div>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
    </button>
</div>

<form action="report_requests_view.php" method="GET" class="mb-4">
    <div class="d-flex align-items-center">
        <label for="status" class="form-label fw-bold me-2 mb-0">Status:</label>
        <select name="status" id="status" class="form-select" style="width: 250px;" onchange="this.form.submit()">
            <option value="Semua" <?php if ($status_filter == 'Semua') echo 'selected'; ?>>Semua Status</option>
            <option value="Belum Diproses" <?php if ($status_filter == 'Belum Diproses') echo 'selected'; ?>>Belum Diproses</option>
            <option value="Diluluskan" <?php if ($status_filter == 'Diluluskan') echo 'selected'; ?>>Diluluskan</option>
            <option value="Ditolak" <?php if ($status_filter == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
            <option value="Selesai" <?php if ($status_filter == 'Selesai') echo 'selected'; ?>>Selesai</option>
        </select>

        <input type="hidden" name="mula" value="<?php echo htmlspecialchars($tarikh_mula); ?>">
        <input type="hidden" name="akhir" value="<?php echo htmlspecialchars($tarikh_akhir); ?>">
    </div>
</form>

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
                        <th>Tarikh Lulus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests && $requests->num_rows > 0): ?>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['ID_permohonan']; ?></td>
                                <td><?php echo format_malay_date($row['tarikh_mohon']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_staf']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk'] ?? '-'); ?></td>
                                <td class="text-center"><?php echo $row['jumlah_diminta'] ?? 0; ?></td>
                                <td><?php echo htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                                <td>
                                    <?php
                                        $status = $row['status'];
                                        $badge_class = 'status-badge';
                                        if ($status == 'Diluluskan') $badge_class .= ' status-diluluskan';
                                        elseif ($status == 'Baru') $badge_class .= ' status-baru';
                                        elseif ($status == 'Ditolak') $badge_class .= ' status-ditolak';
                                    ?>
                                    <span class="<?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td>
                                    <?php echo !empty($row['tarikh_lulus']) ? format_malay_date($row['tarikh_lulus']) : '-'; ?>
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